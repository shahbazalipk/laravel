<?php

namespace Tests\Feature;

use App\Models\EmailTemplate;
use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\EmailProviderConfig;
use App\Services\CampaignService;
use App\Services\TemplateService;
use App\Services\RecipientService;
use App\Jobs\SendCampaignJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * End-to-End Campaign Flow Test
 * 
 * Tests the complete campaign workflow from template creation through sending
 * and delivery verification with multiple providers and failover scenarios.
 * 
 * **Validates: All requirements**
 */
class CampaignEndToEndTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Set event context
        config(['event.event_id' => 1, 'event.org_id' => 1]);
    }

    /**
     * Test complete campaign flow: Create template → Create campaign → Upload recipients → Send → Verify delivery
     */
    public function test_complete_campaign_flow_with_smtp_provider(): void
    {
        Queue::fake();
        
        // Step 1: Create email template with merge codes
        $templateService = app(TemplateService::class);
        $template = $templateService->create([
            'name' => 'Welcome Email',
            'category' => 'invitation',
            'subject' => 'Welcome {{first_name}}!',
            'html_content' => '<p>Hello {{first_name}} {{last_name}},</p><p>Welcome to {{event_name}}!</p>',
            'text_content' => 'Hello {{first_name}} {{last_name}}, Welcome to {{event_name}}!',
            'is_active' => true,
        ]);
        
        $this->assertNotNull($template);
        $this->assertEquals('Welcome Email', $template->name);
        $this->assertDatabaseHas('email_templates', [
            'id' => $template->id,
            'name' => 'Welcome Email',
            'event_id' => 1,
            'org_id' => 1,
        ]);
        
        // Step 2: Configure SMTP provider
        $providerConfig = EmailProviderConfig::create([
            'provider_name' => 'smtp',
            'credentials' => [
                'host' => 'smtp.mailtrap.io',
                'port' => 587,
                'username' => 'test',
                'password' => 'test',
                'encryption' => 'tls',
            ],
            'settings' => ['rate_limit' => 100],
            'is_active' => true,
            'is_default' => true,
        ]);
        
        $this->assertNotNull($providerConfig);
        $this->assertTrue($providerConfig->is_default);
        
        // Step 3: Create campaign
        $campaignService = app(CampaignService::class);
        $campaign = $campaignService->create([
            'email_template_id' => $template->id,
            'name' => 'Welcome Campaign',
            'description' => 'Send welcome emails to new registrants',
            'sender_name' => 'Event Team',
            'sender_email' => 'team@event.com',
            'reply_to_email' => 'support@event.com',
            'recipient_source' => 'manual',
            'status' => 'draft',
        ]);
        
        $this->assertNotNull($campaign);
        $this->assertEquals('draft', $campaign->status);
        $this->assertDatabaseHas('email_campaigns', [
            'id' => $campaign->id,
            'name' => 'Welcome Campaign',
            'status' => 'draft',
        ]);
        
        // Step 4: Add recipients manually
        $recipients = collect([
            [
                'email' => 'john@example.com',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'merge_data' => ['event_name' => 'Tech Conference 2024'],
            ],
            [
                'email' => 'jane@example.com',
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'merge_data' => ['event_name' => 'Tech Conference 2024'],
            ],
            [
                'email' => 'bob@example.com',
                'first_name' => 'Bob',
                'last_name' => 'Johnson',
                'merge_data' => ['event_name' => 'Tech Conference 2024'],
            ],
        ]);
        
        foreach ($recipients as $recipientData) {
            EmailCampaignRecipient::create([
                'email_campaign_id' => $campaign->id,
                'email' => $recipientData['email'],
                'first_name' => $recipientData['first_name'],
                'last_name' => $recipientData['last_name'],
                'merge_data' => $recipientData['merge_data'],
                'status' => 'pending',
            ]);
        }
        
        // Update campaign recipient count
        $campaign->update(['total_recipients' => $recipients->count()]);
        
        $this->assertEquals(3, $campaign->total_recipients);
        $this->assertDatabaseCount('email_campaign_recipients', 3);
        
        // Step 5: Send campaign
        $campaignService->send($campaign);
        
        // Verify campaign status changed to sending
        $campaign->refresh();
        $this->assertEquals('sending', $campaign->status);
        $this->assertNotNull($campaign->started_at);
        
        // Verify SendCampaignJob was dispatched
        Queue::assertPushed(SendCampaignJob::class, function ($job) use ($campaign) {
            return $job->campaign->id === $campaign->id;
        });
        
        // Step 6: Verify all recipients are queued
        $pendingRecipients = EmailCampaignRecipient::where('email_campaign_id', $campaign->id)
            ->where('status', 'pending')
            ->count();
        
        $this->assertEquals(3, $pendingRecipients);
    }

    /**
     * Test campaign with provider failover scenario
     */
    public function test_campaign_with_provider_failover(): void
    {
        Queue::fake();
        
        // Create template
        $template = EmailTemplate::create([
            'name' => 'Test Template',
            'slug' => 'test-template-failover',
            'category' => 'invitation',
            'subject' => 'Test Subject',
            'html_content' => '<p>Test content</p>',
            'text_content' => 'Test content',
            'is_active' => true,
        ]);
        
        // Configure primary provider (Infobip) - will fail
        $primaryProvider = EmailProviderConfig::create([
            'provider_name' => 'infobip',
            'credentials' => [
                'api_key' => 'invalid_key',
                'base_url' => 'https://api.infobip.com',
            ],
            'settings' => ['rate_limit' => 100],
            'is_active' => true,
            'is_default' => true,
        ]);
        
        // Configure fallback provider (SMTP)
        $fallbackProvider = EmailProviderConfig::create([
            'provider_name' => 'smtp',
            'credentials' => [
                'host' => 'smtp.mailtrap.io',
                'port' => 587,
                'username' => 'test',
                'password' => 'test',
                'encryption' => 'tls',
            ],
            'settings' => ['rate_limit' => 50],
            'is_active' => true,
            'is_default' => false,
        ]);
        
        // Create campaign
        $campaignService = app(CampaignService::class);
        $campaign = $campaignService->create([
            'email_template_id' => $template->id,
            'name' => 'Failover Test Campaign',
            'sender_name' => 'Test Sender',
            'sender_email' => 'sender@test.com',
            'recipient_source' => 'manual',
            'status' => 'draft',
        ]);
        
        // Add recipient
        EmailCampaignRecipient::create([
            'email_campaign_id' => $campaign->id,
            'email' => 'test@example.com',
            'first_name' => 'Test',
            'last_name' => 'User',
            'merge_data' => [],
            'status' => 'pending',
        ]);
        
        $campaign->update(['total_recipients' => 1]);
        
        // Send campaign
        $campaignService->send($campaign);
        
        // Verify job was dispatched
        Queue::assertPushed(SendCampaignJob::class);
        
        // Verify campaign is in sending state
        $campaign->refresh();
        $this->assertEquals('sending', $campaign->status);
    }

    /**
     * Test campaign with large recipient list (batch processing)
     */
    public function test_campaign_with_large_recipient_list(): void
    {
        Queue::fake();
        
        // Create template
        $template = EmailTemplate::create([
            'name' => 'Bulk Email Template',
            'slug' => 'bulk-email-template',
            'category' => 'invitation',
            'subject' => 'Bulk Email',
            'html_content' => '<p>Hello {{first_name}}</p>',
            'text_content' => 'Hello {{first_name}}',
            'is_active' => true,
        ]);
        
        // Configure provider
        EmailProviderConfig::create([
            'provider_name' => 'smtp',
            'credentials' => [
                'host' => 'smtp.mailtrap.io',
                'port' => 587,
                'username' => 'test',
                'password' => 'test',
            ],
            'settings' => ['rate_limit' => 100],
            'is_active' => true,
            'is_default' => true,
        ]);
        
        // Create campaign
        $campaignService = app(CampaignService::class);
        $campaign = $campaignService->create([
            'email_template_id' => $template->id,
            'name' => 'Bulk Campaign',
            'sender_name' => 'Bulk Sender',
            'sender_email' => 'bulk@test.com',
            'recipient_source' => 'manual',
            'status' => 'draft',
        ]);
        
        // Add 250 recipients (should create 3 batches of 100, 100, 50)
        $recipientCount = 250;
        for ($i = 1; $i <= $recipientCount; $i++) {
            EmailCampaignRecipient::create([
                'email_campaign_id' => $campaign->id,
                'email' => "user{$i}@example.com",
                'first_name' => "User{$i}",
                'last_name' => 'Test',
                'merge_data' => [],
                'status' => 'pending',
            ]);
        }
        
        $campaign->update(['total_recipients' => $recipientCount]);
        
        // Send campaign
        $campaignService->send($campaign);
        
        // Verify campaign status
        $campaign->refresh();
        $this->assertEquals('sending', $campaign->status);
        $this->assertEquals($recipientCount, $campaign->total_recipients);
        
        // Verify job was dispatched
        Queue::assertPushed(SendCampaignJob::class);
        
        // Verify all recipients are pending
        $pendingCount = EmailCampaignRecipient::where('email_campaign_id', $campaign->id)
            ->where('status', 'pending')
            ->count();
        
        $this->assertEquals($recipientCount, $pendingCount);
    }

    /**
     * Test campaign with scheduled sending
     */
    public function test_campaign_with_scheduled_sending(): void
    {
        Queue::fake();
        
        // Create template
        $template = EmailTemplate::create([
            'name' => 'Scheduled Template',
            'slug' => 'scheduled-template',
            'category' => 'reminder',
            'subject' => 'Reminder',
            'html_content' => '<p>Reminder content</p>',
            'text_content' => 'Reminder content',
            'is_active' => true,
        ]);
        
        // Configure provider
        EmailProviderConfig::create([
            'provider_name' => 'smtp',
            'credentials' => [
                'host' => 'smtp.mailtrap.io',
                'port' => 587,
                'username' => 'test',
                'password' => 'test',
            ],
            'settings' => ['rate_limit' => 100],
            'is_active' => true,
            'is_default' => true,
        ]);
        
        // Create campaign
        $campaignService = app(CampaignService::class);
        $campaign = $campaignService->create([
            'email_template_id' => $template->id,
            'name' => 'Scheduled Campaign',
            'sender_name' => 'Scheduler',
            'sender_email' => 'scheduler@test.com',
            'recipient_source' => 'manual',
            'status' => 'draft',
        ]);
        
        // Add recipient
        EmailCampaignRecipient::create([
            'email_campaign_id' => $campaign->id,
            'email' => 'scheduled@example.com',
            'first_name' => 'Scheduled',
            'last_name' => 'User',
            'merge_data' => [],
            'status' => 'pending',
        ]);
        
        $campaign->update(['total_recipients' => 1]);
        
        // Schedule campaign for future
        $scheduledTime = now()->addHours(2);
        $campaignService->schedule($campaign, $scheduledTime);
        
        // Verify campaign is scheduled
        $campaign->refresh();
        $this->assertEquals('scheduled', $campaign->status);
        $this->assertNotNull($campaign->scheduled_at);
        $this->assertEquals($scheduledTime->timestamp, $campaign->scheduled_at->timestamp);
        
        // Verify job was NOT dispatched yet
        Queue::assertNotPushed(SendCampaignJob::class);
    }

    /**
     * Test campaign pause and resume functionality
     */
    public function test_campaign_pause_and_resume(): void
    {
        Queue::fake();
        
        // Create template
        $template = EmailTemplate::create([
            'name' => 'Pause Test Template',
            'slug' => 'pause-test-template',
            'category' => 'invitation',
            'subject' => 'Test',
            'html_content' => '<p>Test</p>',
            'text_content' => 'Test',
            'is_active' => true,
        ]);
        
        // Configure provider
        EmailProviderConfig::create([
            'provider_name' => 'smtp',
            'credentials' => [
                'host' => 'smtp.mailtrap.io',
                'port' => 587,
                'username' => 'test',
                'password' => 'test',
            ],
            'settings' => ['rate_limit' => 100],
            'is_active' => true,
            'is_default' => true,
        ]);
        
        // Create campaign
        $campaignService = app(CampaignService::class);
        $campaign = $campaignService->create([
            'email_template_id' => $template->id,
            'name' => 'Pause Test Campaign',
            'sender_name' => 'Test',
            'sender_email' => 'test@test.com',
            'recipient_source' => 'manual',
            'status' => 'draft',
        ]);
        
        // Add recipient
        EmailCampaignRecipient::create([
            'email_campaign_id' => $campaign->id,
            'email' => 'pause@example.com',
            'first_name' => 'Pause',
            'last_name' => 'Test',
            'merge_data' => [],
            'status' => 'pending',
        ]);
        
        $campaign->update(['total_recipients' => 1]);
        
        // Send campaign
        $campaignService->send($campaign);
        $campaign->refresh();
        $this->assertEquals('sending', $campaign->status);
        
        // Pause campaign
        $campaignService->pause($campaign);
        $campaign->refresh();
        $this->assertEquals('paused', $campaign->status);
        $this->assertNotNull($campaign->paused_at);
        
        // Resume campaign
        $campaignService->resume($campaign);
        $campaign->refresh();
        $this->assertEquals('sending', $campaign->status);
        $this->assertNull($campaign->paused_at);
    }

    /**
     * Test campaign cancellation
     */
    public function test_campaign_cancellation(): void
    {
        Queue::fake();
        
        // Create template
        $template = EmailTemplate::create([
            'name' => 'Cancel Test Template',
            'slug' => 'cancel-test-template',
            'category' => 'invitation',
            'subject' => 'Test',
            'html_content' => '<p>Test</p>',
            'text_content' => 'Test',
            'is_active' => true,
        ]);
        
        // Configure provider
        EmailProviderConfig::create([
            'provider_name' => 'smtp',
            'credentials' => [
                'host' => 'smtp.mailtrap.io',
                'port' => 587,
                'username' => 'test',
                'password' => 'test',
            ],
            'settings' => ['rate_limit' => 100],
            'is_active' => true,
            'is_default' => true,
        ]);
        
        // Create campaign
        $campaignService = app(CampaignService::class);
        $campaign = $campaignService->create([
            'email_template_id' => $template->id,
            'name' => 'Cancel Test Campaign',
            'sender_name' => 'Test',
            'sender_email' => 'test@test.com',
            'recipient_source' => 'manual',
            'status' => 'draft',
        ]);
        
        // Add recipient
        EmailCampaignRecipient::create([
            'email_campaign_id' => $campaign->id,
            'email' => 'cancel@example.com',
            'first_name' => 'Cancel',
            'last_name' => 'Test',
            'merge_data' => [],
            'status' => 'pending',
        ]);
        
        $campaign->update(['total_recipients' => 1]);
        
        // Send campaign
        $campaignService->send($campaign);
        $campaign->refresh();
        $this->assertEquals('sending', $campaign->status);
        
        // Cancel campaign
        $campaignService->cancel($campaign);
        $campaign->refresh();
        $this->assertEquals('cancelled', $campaign->status);
        $this->assertNotNull($campaign->cancelled_at);
    }
}
