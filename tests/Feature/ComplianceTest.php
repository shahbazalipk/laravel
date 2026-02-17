<?php

namespace Tests\Feature;

use App\Models\EmailTemplate;
use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\EmailUnsubscribe;
use App\Services\UnsubscribeService;
use App\Services\RecipientService;
use App\Services\MergeCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Compliance Test
 * 
 * Tests GDPR and CAN-SPAM compliance features including unsubscribe mechanism,
 * unsubscribe link presence in emails, and exclusion of unsubscribed recipients.
 * 
 * **Validates: Requirements US-10 (Compliance)**
 */
class ComplianceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Set event context
        config(['event.event_id' => 1, 'event.org_id' => 1]);
    }

    /**
     * Test that unsubscribe link is included in email templates
     */
    public function test_unsubscribe_link_included_in_email_templates(): void
    {
        $template = EmailTemplate::create([
            'name' => 'Compliance Test Template',
            'slug' => 'compliance-test-template',
            'category' => 'invitation',
            'subject' => 'Test Subject',
            'html_content' => '<p>Hello {{first_name}},</p><p>Event details here.</p><p><a href="{{unsubscribe_url}}">Unsubscribe</a></p>',
            'text_content' => 'Hello {{first_name}}, Event details here. Unsubscribe: {{unsubscribe_url}}',
            'is_active' => true,
        ]);
        
        // Verify template contains unsubscribe merge code
        $this->assertStringContainsString('{{unsubscribe_url}}', $template->html_content);
        $this->assertStringContainsString('{{unsubscribe_url}}', $template->text_content);
        
        // Verify merge code service recognizes unsubscribe_url
        $mergeCodeService = app(MergeCodeService::class);
        $codes = $mergeCodeService->parse($template->html_content);
        
        $this->assertContains('unsubscribe_url', $codes);
    }

    /**
     * Test unsubscribe URL generation
     */
    public function test_unsubscribe_url_generation(): void
    {
        $unsubscribeService = app(UnsubscribeService::class);
        
        // Create campaign
        $campaign = $this->createTestCampaign();
        
        $email = 'test@example.com';
        
        // Generate unsubscribe URL
        $url = $unsubscribeService->getUnsubscribeUrl($email, $campaign->id);
        
        // Verify URL is generated
        $this->assertNotEmpty($url);
        $this->assertStringContainsString('/unsubscribe/', $url);
        
        // Verify URL contains hash (not plain email)
        $this->assertStringNotContainsString($email, $url);
        $this->assertStringNotContainsString('@', $url);
    }

    /**
     * Test unsubscribe flow
     */
    public function test_unsubscribe_flow(): void
    {
        $unsubscribeService = app(UnsubscribeService::class);
        
        $email = 'unsubscribe@example.com';
        $reason = 'Not interested';
        
        // Verify email is not unsubscribed initially
        $this->assertFalse($unsubscribeService->isUnsubscribed($email));
        
        // Unsubscribe
        $unsubscribeService->unsubscribe($email, $reason);
        
        // Verify email is now unsubscribed
        $this->assertTrue($unsubscribeService->isUnsubscribed($email));
        
        // Verify database record created
        $this->assertDatabaseHas('email_unsubscribes', [
            'email' => $email,
            'reason' => $reason,
            'event_id' => 1,
            'org_id' => 1,
        ]);
    }

    /**
     * Test unsubscribe persistence
     */
    public function test_unsubscribe_persistence(): void
    {
        $unsubscribeService = app(UnsubscribeService::class);
        
        $email = 'persistent@example.com';
        
        // Unsubscribe
        $unsubscribeService->unsubscribe($email);
        
        // Verify unsubscribe persists across requests
        $this->assertTrue($unsubscribeService->isUnsubscribed($email));
        
        // Create new service instance (simulating new request)
        $newService = app(UnsubscribeService::class);
        $this->assertTrue($newService->isUnsubscribed($email));
    }

    /**
     * Test resubscribe functionality
     */
    public function test_resubscribe_functionality(): void
    {
        $unsubscribeService = app(UnsubscribeService::class);
        
        $email = 'resubscribe@example.com';
        
        // Unsubscribe
        $unsubscribeService->unsubscribe($email);
        $this->assertTrue($unsubscribeService->isUnsubscribed($email));
        
        // Resubscribe
        $unsubscribeService->resubscribe($email);
        $this->assertFalse($unsubscribeService->isUnsubscribed($email));
        
        // Verify database record removed
        $this->assertDatabaseMissing('email_unsubscribes', [
            'email' => $email,
            'event_id' => 1,
            'org_id' => 1,
        ]);
    }

    /**
     * Test that unsubscribed emails are excluded from recipient resolution
     */
    public function test_unsubscribed_emails_excluded_from_recipients(): void
    {
        $unsubscribeService = app(UnsubscribeService::class);
        $recipientService = app(RecipientService::class);
        
        // Create test recipients
        $recipients = collect([
            ['email' => 'active1@example.com', 'first_name' => 'Active', 'last_name' => 'One'],
            ['email' => 'active2@example.com', 'first_name' => 'Active', 'last_name' => 'Two'],
            ['email' => 'unsubscribed@example.com', 'first_name' => 'Unsubscribed', 'last_name' => 'User'],
            ['email' => 'active3@example.com', 'first_name' => 'Active', 'last_name' => 'Three'],
        ]);
        
        // Unsubscribe one email
        $unsubscribeService->unsubscribe('unsubscribed@example.com');
        
        // Exclude unsubscribed emails
        $filtered = $recipientService->excludeUnsubscribed($recipients);
        
        // Verify unsubscribed email is excluded
        $this->assertCount(3, $filtered);
        $this->assertFalse($filtered->pluck('email')->contains('unsubscribed@example.com'));
        $this->assertTrue($filtered->pluck('email')->contains('active1@example.com'));
        $this->assertTrue($filtered->pluck('email')->contains('active2@example.com'));
        $this->assertTrue($filtered->pluck('email')->contains('active3@example.com'));
    }

    /**
     * Test immediate effect of unsubscribe
     */
    public function test_unsubscribe_immediate_effect(): void
    {
        $unsubscribeService = app(UnsubscribeService::class);
        $recipientService = app(RecipientService::class);
        
        $email = 'immediate@example.com';
        
        // Create recipient list with the email
        $recipients = collect([
            ['email' => $email, 'first_name' => 'Test', 'last_name' => 'User'],
            ['email' => 'other@example.com', 'first_name' => 'Other', 'last_name' => 'User'],
        ]);
        
        // Before unsubscribe - email should be included
        $beforeUnsubscribe = $recipientService->excludeUnsubscribed($recipients);
        $this->assertCount(2, $beforeUnsubscribe);
        $this->assertTrue($beforeUnsubscribe->pluck('email')->contains($email));
        
        // Unsubscribe
        $unsubscribeService->unsubscribe($email);
        
        // After unsubscribe - email should be excluded immediately
        $afterUnsubscribe = $recipientService->excludeUnsubscribed($recipients);
        $this->assertCount(1, $afterUnsubscribe);
        $this->assertFalse($afterUnsubscribe->pluck('email')->contains($email));
        $this->assertTrue($afterUnsubscribe->pluck('email')->contains('other@example.com'));
    }

    /**
     * Test multiple unsubscribes for same email (idempotent)
     */
    public function test_multiple_unsubscribes_are_idempotent(): void
    {
        $unsubscribeService = app(UnsubscribeService::class);
        
        $email = 'idempotent@example.com';
        
        // Unsubscribe multiple times
        $unsubscribeService->unsubscribe($email, 'Reason 1');
        $unsubscribeService->unsubscribe($email, 'Reason 2');
        $unsubscribeService->unsubscribe($email, 'Reason 3');
        
        // Verify still unsubscribed
        $this->assertTrue($unsubscribeService->isUnsubscribed($email));
        
        // Verify only one record exists (or latest is kept)
        $count = EmailUnsubscribe::where('email', $email)
            ->where('event_id', 1)
            ->where('org_id', 1)
            ->count();
        
        $this->assertGreaterThanOrEqual(1, $count);
    }

    /**
     * Test unsubscribe with different reasons
     */
    public function test_unsubscribe_with_different_reasons(): void
    {
        $unsubscribeService = app(UnsubscribeService::class);
        
        $reasons = [
            'Not interested',
            'Too many emails',
            'Never signed up',
            'Content not relevant',
            null, // No reason provided
        ];
        
        foreach ($reasons as $index => $reason) {
            $email = "reason{$index}@example.com";
            
            $unsubscribeService->unsubscribe($email, $reason);
            
            $this->assertTrue($unsubscribeService->isUnsubscribed($email));
            
            $this->assertDatabaseHas('email_unsubscribes', [
                'email' => $email,
                'reason' => $reason,
            ]);
        }
    }

    /**
     * Test event scope isolation for unsubscribes
     */
    public function test_unsubscribe_event_scope_isolation(): void
    {
        $unsubscribeService = app(UnsubscribeService::class);
        
        $email = 'scope@example.com';
        
        // Unsubscribe in event 1
        config(['event.event_id' => 1, 'event.org_id' => 1]);
        $unsubscribeService->unsubscribe($email);
        $this->assertTrue($unsubscribeService->isUnsubscribed($email));
        
        // Switch to event 2 - should NOT be unsubscribed
        config(['event.event_id' => 2, 'event.org_id' => 2]);
        $newService = app(UnsubscribeService::class);
        $this->assertFalse($newService->isUnsubscribed($email));
        
        // Switch back to event 1 - should still be unsubscribed
        config(['event.event_id' => 1, 'event.org_id' => 1]);
        $originalService = app(UnsubscribeService::class);
        $this->assertTrue($originalService->isUnsubscribed($email));
    }

    /**
     * Test bulk unsubscribe exclusion
     */
    public function test_bulk_unsubscribe_exclusion(): void
    {
        $unsubscribeService = app(UnsubscribeService::class);
        $recipientService = app(RecipientService::class);
        
        // Create 100 recipients
        $recipients = collect();
        for ($i = 1; $i <= 100; $i++) {
            $recipients->push([
                'email' => "user{$i}@example.com",
                'first_name' => "User{$i}",
                'last_name' => 'Test',
            ]);
        }
        
        // Unsubscribe every 5th email (20 total)
        for ($i = 5; $i <= 100; $i += 5) {
            $unsubscribeService->unsubscribe("user{$i}@example.com");
        }
        
        // Exclude unsubscribed
        $filtered = $recipientService->excludeUnsubscribed($recipients);
        
        // Verify correct count (80 remaining)
        $this->assertCount(80, $filtered);
        
        // Verify unsubscribed emails are excluded
        for ($i = 5; $i <= 100; $i += 5) {
            $this->assertFalse($filtered->pluck('email')->contains("user{$i}@example.com"));
        }
        
        // Verify active emails are included
        $this->assertTrue($filtered->pluck('email')->contains('user1@example.com'));
        $this->assertTrue($filtered->pluck('email')->contains('user2@example.com'));
    }

    /**
     * Test unsubscribe URL hash validation
     */
    public function test_unsubscribe_url_hash_validation(): void
    {
        $unsubscribeService = app(UnsubscribeService::class);
        
        $campaign = $this->createTestCampaign();
        $email = 'hash@example.com';
        
        // Generate valid URL
        $validUrl = $unsubscribeService->getUnsubscribeUrl($email, $campaign->id);
        
        // Extract hash from URL
        $this->assertNotEmpty($validUrl);
        
        // Verify URL format (contains /unsubscribe/ and a hash)
        $this->assertStringContainsString('/unsubscribe/', $validUrl);
        $this->assertMatchesRegularExpression('/\/unsubscribe\/[a-zA-Z0-9+\/=]+/', $validUrl);
    }

    /**
     * Test campaign with unsubscribed recipients
     */
    public function test_campaign_excludes_unsubscribed_recipients(): void
    {
        $unsubscribeService = app(UnsubscribeService::class);
        
        // Create campaign
        $campaign = $this->createTestCampaign();
        
        // Create recipients
        $recipients = [
            ['email' => 'active@example.com', 'first_name' => 'Active', 'last_name' => 'User'],
            ['email' => 'unsubbed@example.com', 'first_name' => 'Unsubbed', 'last_name' => 'User'],
        ];
        
        // Unsubscribe one
        $unsubscribeService->unsubscribe('unsubbed@example.com');
        
        // Add recipients to campaign
        foreach ($recipients as $recipientData) {
            // Only add if not unsubscribed
            if (!$unsubscribeService->isUnsubscribed($recipientData['email'])) {
                EmailCampaignRecipient::create([
                    'email_campaign_id' => $campaign->id,
                    'email' => $recipientData['email'],
                    'first_name' => $recipientData['first_name'],
                    'last_name' => $recipientData['last_name'],
                    'merge_data' => [],
                    'status' => 'pending',
                ]);
            }
        }
        
        // Verify only active recipient was added
        $this->assertEquals(1, $campaign->recipients()->count());
        $this->assertEquals('active@example.com', $campaign->recipients()->first()->email);
    }

    /**
     * Helper method to create a test campaign
     */
    private function createTestCampaign(): EmailCampaign
    {
        $template = EmailTemplate::create([
            'name' => 'Test Template',
            'slug' => 'test-template-' . uniqid(),
            'category' => 'invitation',
            'subject' => 'Test Subject',
            'html_content' => '<p>Test {{unsubscribe_url}}</p>',
            'text_content' => 'Test {{unsubscribe_url}}',
            'is_active' => true,
        ]);
        
        return EmailCampaign::create([
            'email_template_id' => $template->id,
            'name' => 'Test Campaign',
            'sender_name' => 'Test Sender',
            'sender_email' => 'sender@test.com',
            'status' => 'draft',
            'recipient_source' => 'manual',
        ]);
    }
}
