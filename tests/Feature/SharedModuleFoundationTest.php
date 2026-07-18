<?php

namespace Tests\Feature;

use App\Shared\Audit\AuditLogger;
use App\Shared\Audit\Models\PlatformAuditEntry;
use App\Shared\Authorization\Models\ModuleAccessGrant;
use App\Shared\Authorization\ModuleAuthorizer;
use App\Shared\Files\AttachmentService;
use App\Shared\Integration\ResourceLinker;
use App\Shared\Integration\ResourceReference;
use App\Shared\Notifications\NotificationService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use LogicException;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

class SharedModuleFoundationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $migration = require database_path('migrations/2026_07_18_050000_create_shared_module_foundation_tables.php');
        $migration->up();

        config([
            'event.event_id' => 10,
            'event.org_id' => 20,
            'modules.attachments.disk' => 'module-tests',
        ]);
        session([
            'admin_logged_in' => true,
            'admin_id' => 55,
            'admin_name' => 'Finance Admin',
            'admin_email' => 'finance@example.com',
            'admin_type' => 'organization_admin',
            'admin_is_primary' => false,
        ]);
    }

    protected function tearDown(): void
    {
        $migration = require database_path('migrations/2026_07_18_050000_create_shared_module_foundation_tables.php');
        $migration->down();

        parent::tearDown();
    }

    #[Test]
    public function shared_records_are_fail_closed_and_tenant_isolated(): void
    {
        ModuleAccessGrant::query()->create([
            'organization_admin_user_id' => 55,
            'module' => 'finance',
            'role' => 'accountant',
            'abilities' => ['finance.view'],
        ]);

        $this->assertSame(1, ModuleAccessGrant::query()->count());

        config(['event.event_id' => 11, 'event.org_id' => 20]);
        $this->assertSame(0, ModuleAccessGrant::query()->count());

        config(['event.event_id' => null, 'event.org_id' => null]);
        $this->expectException(RuntimeException::class);
        ModuleAccessGrant::query()->count();
    }

    #[Test]
    public function module_authorization_denies_by_default_and_honors_persisted_abilities(): void
    {
        $authorizer = app(ModuleAuthorizer::class);

        $this->assertFalse($authorizer->allows('finance', 'finance.view'));

        ModuleAccessGrant::query()->create([
            'organization_admin_user_id' => 55,
            'module' => 'finance',
            'role' => 'viewer',
            'abilities' => ['finance.view'],
        ]);

        $this->assertTrue($authorizer->allows('finance', 'finance.view'));
        $this->assertFalse($authorizer->allows('finance', 'finance.pay'));

        session(['admin_is_primary' => true]);
        $this->assertTrue($authorizer->allows('finance', 'finance.pay'));
    }

    #[Test]
    public function audit_entries_are_redacted_and_immutable(): void
    {
        $subject = ModuleAccessGrant::query()->create([
            'organization_admin_user_id' => 55,
            'module' => 'finance',
            'role' => 'manager',
            'abilities' => ['*'],
        ]);

        $entry = app(AuditLogger::class)->record(
            'finance',
            'account.created',
            $subject,
            [],
            ['name' => 'Operating Account', 'iban' => 'PK00SECRET'],
            idempotencyKey: 'account-created-'.$subject->public_id,
        );

        $this->assertSame('[REDACTED]', $entry->after_values['iban']);
        $this->assertSame('Operating Account', $entry->after_values['name']);

        $this->expectException(LogicException::class);
        $entry->update(['reason' => 'tampered']);
    }

    #[Test]
    public function attachments_are_private_tenant_scoped_and_recoverable_after_soft_delete(): void
    {
        Storage::fake('module-tests');
        $attachment = app(AttachmentService::class)->store(
            UploadedFile::fake()->image('receipt.jpg'),
            'finance',
            'expense',
            'expense-public-id',
            'receipt',
            true,
        );

        Storage::disk('module-tests')->assertExists($attachment->path);
        $this->assertTrue($attachment->is_sensitive);
        $this->assertSame('restricted', $attachment->visibility);

        app(AttachmentService::class)->delete($attachment);
        Storage::disk('module-tests')->assertExists($attachment->path);
        $this->assertSoftDeleted('platform_attachments', ['id' => $attachment->id]);
    }

    #[Test]
    public function notifications_deduplicate_and_cross_module_links_are_explicit(): void
    {
        $notifications = app(NotificationService::class);
        $first = $notifications->send(
            55,
            'finance',
            'expense.approval.requested',
            'Expense approval requested',
            deduplicationKey: 'expense-1-approval',
        );
        $second = $notifications->send(
            55,
            'finance',
            'expense.approval.requested',
            'Expense approval requested',
            deduplicationKey: 'expense-1-approval',
        );

        $this->assertTrue($first->is($second));
        $this->assertDatabaseCount('platform_notifications', 1);

        $link = app(ResourceLinker::class)->link(
            new ResourceReference('projects', 'task', 'task-1'),
            new ResourceReference('finance', 'expense', 'expense-1'),
            'funded_by',
        );

        $this->assertSame('funded_by', $link->relationship);
        $this->assertSame('task-1', $link->source_public_id);
        $this->assertSame('expense-1', $link->target_public_id);
    }

    #[Test]
    public function audit_entries_cannot_be_deleted(): void
    {
        $entry = PlatformAuditEntry::query()->create([
            'module' => 'projects',
            'action' => 'project.created',
            'subject_type' => 'project',
            'subject_public_id' => 'project-1',
            'occurred_at' => now(),
        ]);

        $this->expectException(LogicException::class);
        $entry->delete();
    }
}
