<?php

namespace Tests\Feature;

use App\Models\Industry;
use App\Services\AuditService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class IndustryBulkImportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['event.event_id' => 1, 'event.org_id' => 1]);

        Schema::dropIfExists('industries');
        Schema::dropIfExists('hash_mappings');

        Schema::create('hash_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('hash', 64)->unique();
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->timestamps();
        });

        Schema::create('industries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('name');
            $table->string('slug')->nullable();
            $table->string('color')->nullable();
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        $audit = Mockery::mock(AuditService::class);
        $audit->shouldReceive('log')->andReturnNull();
        $this->app->instance(AuditService::class, $audit);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('industries');
        Schema::dropIfExists('hash_mappings');
        Mockery::close();
        parent::tearDown();
    }

    private function actingAsAdmin()
    {
        return $this->withSession([
            'admin_logged_in' => true,
            'admin_id' => 1,
            'admin_email' => 'admin@test.com',
            'event_id' => 1,
            'org_id' => 1,
        ]);
    }

    public function test_index_shows_bulk_import_button(): void
    {
        $response = $this->actingAsAdmin()
            ->get(route('admin.industries.index'));

        $response->assertOk();
        $response->assertSee('Bulk Import');
        $response->assertSee('data-testid="bulk-import-modal"', false);
    }

    public function test_bulk_import_from_paste(): void
    {
        $response = $this->actingAsAdmin()
            ->post(route('admin.industries.bulk-import'), [
                'names' => "Technology\nHealthcare\nFinance",
                'color' => '#6366f1',
                'sort_order' => 5,
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('admin.industries.index'));
        $response->assertSessionHas('success');

        $this->assertEquals(3, Industry::count());
        $this->assertDatabaseHas('industries', [
            'name' => 'Technology',
            'sort_order' => 5,
            'is_active' => 1,
        ]);
    }

    public function test_bulk_import_from_file(): void
    {
        $file = UploadedFile::fake()->createWithContent(
            'industries.csv',
            "Aerospace\nDefense\n"
        );

        $response = $this->actingAsAdmin()
            ->post(route('admin.industries.bulk-import'), [
                'import_file' => $file,
                'is_active' => '1',
                'sort_order' => 0,
                'color' => '#10b981',
            ]);

        $response->assertRedirect(route('admin.industries.index'));
        $response->assertSessionHas('success');
        $this->assertEquals(2, Industry::count());
    }

    public function test_bulk_import_requires_paste_or_file(): void
    {
        $response = $this->actingAsAdmin()
            ->post(route('admin.industries.bulk-import'), [
                'names' => '   ',
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('admin.industries.index'));
        $response->assertSessionHas('error');
        $this->assertEquals(0, Industry::count());
    }

    public function test_bulk_import_requires_authentication(): void
    {
        $response = $this->post(route('admin.industries.bulk-import'), [
            'names' => "Technology",
        ]);

        $response->assertRedirect(route('admin.login'));
    }

    public function test_bulk_import_reports_skipped_duplicates(): void
    {
        Industry::create([
            'name' => 'Technology',
            'slug' => 'technology',
            'event_id' => 1,
            'org_id' => 1,
        ]);

        $response = $this->actingAsAdmin()
            ->post(route('admin.industries.bulk-import'), [
                'names' => "Technology\nRetail",
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('admin.industries.index'));
        $response->assertSessionHas('success', function ($message) {
            return str_contains($message, '1 industr')
                && str_contains($message, '1 skipped');
        });

        $this->assertEquals(2, Industry::count());
    }
}
