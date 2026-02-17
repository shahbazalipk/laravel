<?php

namespace Tests\Feature;

use App\Models\EmailTemplate;
use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\EmailCampaignLog;
use App\Models\EmailProviderConfig;
use App\Services\TrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Webhook Integration Test
 * 
 * Tests webhook processing for email tracking events from providers.
 * Verifies that webhooks are properly validated, parsed, and logged.
 * 
 * **Validates: Requirements AC-2.4**
 */
class WebhookIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Set event context
        config(['event.event_id' => 1, 'event.org_id' => 1]);
    }

    /**
     * Test Infobip webhook for delivered event
     */
    public function test_infobip_webhook_delivered_event(): void
    {
        // Create campaign and recipient
        [$campaign, $recipient] = $this->createCampaignWithRecipient();
        
        // Simulate email sent
        $messageId = 'infobip-msg-' . uniqid();
        $recipient->update([
            'status' => 'sent',
            'message_id' => $messageId,
            'sent_at' => now(),
        ]);
        
        // Simulate Infobip webhook payload for delivered event
        $webhookPayload = [
            'results' => [
                [
                    'messageId' => $messageId,
                    'to' => $recipient->email,
                    'status' => [
                        'groupId' => 3,
                        'groupName' => 'DELIVERED',
                        'id' => 5,
                        'name' => 'DELIVERED_TO_HANDSET',
                        'description' => 'Message delivered to handset',
                    ],
                    'price' => [
                        'pricePerMessage' => 0.01,
                        'currency' => 'USD',
                    ],
                    'sentAt' => now()->toIso8601String(),
                    'doneAt' => now()->toIso8601String(),
                ],
            ],
        ];
        
        // Send webhook request
        $response = $this->postJson('/event/webhooks/infobip', $webhookPayload);
        
        // Verify response
        $response->assertStatus(200);
        
        // Verify recipient status updated
        $recipient->refresh();
        $this->assertEquals('delivered', $recipient->status);
        $this->assertNotNull($recipient->delivered_at);
        
        // Verify campaign statistics updated
        $campaign->refresh();
        $this->assertEquals(1, $campaign->delivered_count);
        
        // Verify log entry created
        $this->assertDatabaseHas('email_campaign_logs', [
            'email_campaign_id' => $campaign->id,
            'email_campaign_recipient_id' => $recipient->id,
            'event_type' => 'delivered',
            'message_id' => $messageId,
            'email' => $recipient->email,
        ]);
    }

    /**
     * Test Infobip webhook for opened event
     */
    public function test_infobip_webhook_opened_event(): void
    {
        // Create campaign and recipient
        [$campaign, $recipient] = $this->createCampaignWithRecipient();
        
        // Simulate email delivered
        $messageId = 'infobip-msg-' . uniqid();
        $recipient->update([
            'status' => 'delivered',
            'message_id' => $messageId,
            'sent_at' => now()->subMinutes(5),
            'delivered_at' => now()->subMinutes(4),
        ]);
        
        // Simulate Infobip webhook payload for opened event
        $webhookPayload = [
            'results' => [
                [
                    'messageId' => $messageId,
                    'to' => $recipient->email,
                    'event' => 'OPEN',
                    'openedAt' => now()->toIso8601String(),
                ],
            ],
        ];
        
        // Send webhook request
        $response = $this->postJson('/event/webhooks/infobip', $webhookPayload);
        
        // Verify response
        $response->assertStatus(200);
        
        // Verify recipient status updated
        $recipient->refresh();
        $this->assertEquals('opened', $recipient->status);
        $this->assertNotNull($recipient->opened_at);
        
        // Verify campaign statistics updated
        $campaign->refresh();
        $this->assertEquals(1, $campaign->opened_count);
        
        // Verify log entry created
        $this->assertDatabaseHas('email_campaign_logs', [
            'email_campaign_id' => $campaign->id,
            'email_campaign_recipient_id' => $recipient->id,
            'event_type' => 'opened',
            'message_id' => $messageId,
            'email' => $recipient->email,
        ]);
    }

    /**
     * Test Infobip webhook for clicked event
     */
    public function test_infobip_webhook_clicked_event(): void
    {
        // Create campaign and recipient
        [$campaign, $recipient] = $this->createCampaignWithRecipient();
        
        // Simulate email opened
        $messageId = 'infobip-msg-' . uniqid();
        $recipient->update([
            'status' => 'opened',
            'message_id' => $messageId,
            'sent_at' => now()->subMinutes(10),
            'delivered_at' => now()->subMinutes(8),
            'opened_at' => now()->subMinutes(5),
        ]);
        
        $clickedUrl = 'https://example.com/register';
        
        // Simulate Infobip webhook payload for clicked event
        $webhookPayload = [
            'results' => [
                [
                    'messageId' => $messageId,
                    'to' => $recipient->email,
                    'event' => 'CLICK',
                    'clickedAt' => now()->toIso8601String(),
                    'url' => $clickedUrl,
                ],
            ],
        ];
        
        // Send webhook request
        $response = $this->postJson('/event/webhooks/infobip', $webhookPayload);
        
        // Verify response
        $response->assertStatus(200);
        
        // Verify recipient status updated
        $recipient->refresh();
        $this->assertEquals('clicked', $recipient->status);
        $this->assertNotNull($recipient->clicked_at);
        
        // Verify campaign statistics updated
        $campaign->refresh();
        $this->assertEquals(1, $campaign->clicked_count);
        
        // Verify log entry created with URL
        $this->assertDatabaseHas('email_campaign_logs', [
            'email_campaign_id' => $campaign->id,
            'email_campaign_recipient_id' => $recipient->id,
            'event_type' => 'clicked',
            'message_id' => $messageId,
            'email' => $recipient->email,
            'url' => $clickedUrl,
        ]);
    }

    /**
     * Test Infobip webhook for bounced event
     */
    public function test_infobip_webhook_bounced_event(): void
    {
        // Create campaign and recipient
        [$campaign, $recipient] = $this->createCampaignWithRecipient();
        
        // Simulate email sent
        $messageId = 'infobip-msg-' . uniqid();
        $recipient->update([
            'status' => 'sent',
            'message_id' => $messageId,
            'sent_at' => now(),
        ]);
        
        // Simulate Infobip webhook payload for bounced event
        $webhookPayload = [
            'results' => [
                [
                    'messageId' => $messageId,
                    'to' => $recipient->email,
                    'status' => [
                        'groupId' => 5,
                        'groupName' => 'REJECTED',
                        'id' => 27,
                        'name' => 'REJECTED_RECIPIENT',
                        'description' => 'Recipient rejected',
                    ],
                    'error' => [
                        'groupId' => 1,
                        'groupName' => 'HANDSET_ERRORS',
                        'id' => 27,
                        'name' => 'REJECTED_RECIPIENT',
                        'description' => 'Recipient rejected',
                        'permanent' => true,
                    ],
                ],
            ],
        ];
        
        // Send webhook request
        $response = $this->postJson('/event/webhooks/infobip', $webhookPayload);
        
        // Verify response
        $response->assertStatus(200);
        
        // Verify recipient status updated
        $recipient->refresh();
        $this->assertEquals('bounced', $recipient->status);
        $this->assertNotNull($recipient->bounced_at);
        
        // Verify campaign statistics updated
        $campaign->refresh();
        $this->assertEquals(1, $campaign->bounced_count);
        
        // Verify log entry created
        $this->assertDatabaseHas('email_campaign_logs', [
            'email_campaign_id' => $campaign->id,
            'email_campaign_recipient_id' => $recipient->id,
            'event_type' => 'bounced',
            'message_id' => $messageId,
            'email' => $recipient->email,
        ]);
    }

    /**
     * Test Mailchimp webhook for delivered event
     */
    public function test_mailchimp_webhook_delivered_event(): void
    {
        // Create campaign and recipient
        [$campaign, $recipient] = $this->createCampaignWithRecipient();
        
        // Simulate email sent
        $messageId = 'mailchimp-msg-' . uniqid();
        $recipient->update([
            'status' => 'sent',
            'message_id' => $messageId,
            'sent_at' => now(),
        ]);
        
        // Simulate Mailchimp webhook payload for delivered event
        $webhookPayload = [
            'event' => 'send',
            'msg' => [
                '_id' => $messageId,
                'email' => $recipient->email,
                'subject' => 'Test Subject',
                'state' => 'sent',
                'ts' => now()->timestamp,
            ],
        ];
        
        // Send webhook request
        $response = $this->postJson('/event/webhooks/mailchimp', $webhookPayload);
        
        // Verify response
        $response->assertStatus(200);
        
        // Verify recipient status updated
        $recipient->refresh();
        $this->assertEquals('delivered', $recipient->status);
        $this->assertNotNull($recipient->delivered_at);
        
        // Verify campaign statistics updated
        $campaign->refresh();
        $this->assertEquals(1, $campaign->delivered_count);
    }

    /**
     * Test Mailchimp webhook for opened event
     */
    public function test_mailchimp_webhook_opened_event(): void
    {
        // Create campaign and recipient
        [$campaign, $recipient] = $this->createCampaignWithRecipient();
        
        // Simulate email delivered
        $messageId = 'mailchimp-msg-' . uniqid();
        $recipient->update([
            'status' => 'delivered',
            'message_id' => $messageId,
            'sent_at' => now()->subMinutes(5),
            'delivered_at' => now()->subMinutes(4),
        ]);
        
        // Simulate Mailchimp webhook payload for opened event
        $webhookPayload = [
            'event' => 'open',
            'msg' => [
                '_id' => $messageId,
                'email' => $recipient->email,
                'ts' => now()->timestamp,
            ],
        ];
        
        // Send webhook request
        $response = $this->postJson('/event/webhooks/mailchimp', $webhookPayload);
        
        // Verify response
        $response->assertStatus(200);
        
        // Verify recipient status updated
        $recipient->refresh();
        $this->assertEquals('opened', $recipient->status);
        $this->assertNotNull($recipient->opened_at);
        
        // Verify campaign statistics updated
        $campaign->refresh();
        $this->assertEquals(1, $campaign->opened_count);
    }

    /**
     * Test Mailchimp webhook for clicked event
     */
    public function test_mailchimp_webhook_clicked_event(): void
    {
        // Create campaign and recipient
        [$campaign, $recipient] = $this->createCampaignWithRecipient();
        
        // Simulate email opened
        $messageId = 'mailchimp-msg-' . uniqid();
        $recipient->update([
            'status' => 'opened',
            'message_id' => $messageId,
            'sent_at' => now()->subMinutes(10),
            'delivered_at' => now()->subMinutes(8),
            'opened_at' => now()->subMinutes(5),
        ]);
        
        $clickedUrl = 'https://example.com/register';
        
        // Simulate Mailchimp webhook payload for clicked event
        $webhookPayload = [
            'event' => 'click',
            'msg' => [
                '_id' => $messageId,
                'email' => $recipient->email,
                'ts' => now()->timestamp,
                'clicks' => [
                    ['url' => $clickedUrl, 'ts' => now()->timestamp],
                ],
            ],
        ];
        
        // Send webhook request
        $response = $this->postJson('/event/webhooks/mailchimp', $webhookPayload);
        
        // Verify response
        $response->assertStatus(200);
        
        // Verify recipient status updated
        $recipient->refresh();
        $this->assertEquals('clicked', $recipient->status);
        $this->assertNotNull($recipient->clicked_at);
        
        // Verify campaign statistics updated
        $campaign->refresh();
        $this->assertEquals(1, $campaign->clicked_count);
    }

    /**
     * Test Mailchimp webhook for bounced event
     */
    public function test_mailchimp_webhook_bounced_event(): void
    {
        // Create campaign and recipient
        [$campaign, $recipient] = $this->createCampaignWithRecipient();
        
        // Simulate email sent
        $messageId = 'mailchimp-msg-' . uniqid();
        $recipient->update([
            'status' => 'sent',
            'message_id' => $messageId,
            'sent_at' => now(),
        ]);
        
        // Simulate Mailchimp webhook payload for bounced event
        $webhookPayload = [
            'event' => 'hard_bounce',
            'msg' => [
                '_id' => $messageId,
                'email' => $recipient->email,
                'ts' => now()->timestamp,
                'bounce_description' => 'bad_mailbox',
                'diag' => 'smtp;550 5.1.1 The email account does not exist',
            ],
        ];
        
        // Send webhook request
        $response = $this->postJson('/event/webhooks/mailchimp', $webhookPayload);
        
        // Verify response
        $response->assertStatus(200);
        
        // Verify recipient status updated
        $recipient->refresh();
        $this->assertEquals('bounced', $recipient->status);
        $this->assertNotNull($recipient->bounced_at);
        
        // Verify campaign statistics updated
        $campaign->refresh();
        $this->assertEquals(1, $campaign->bounced_count);
    }

    /**
     * Test webhook with invalid message ID (not found)
     */
    public function test_webhook_with_invalid_message_id(): void
    {
        // Simulate Infobip webhook with non-existent message ID
        $webhookPayload = [
            'results' => [
                [
                    'messageId' => 'non-existent-message-id',
                    'to' => 'unknown@example.com',
                    'status' => [
                        'groupId' => 3,
                        'groupName' => 'DELIVERED',
                    ],
                ],
            ],
        ];
        
        // Send webhook request
        $response = $this->postJson('/event/webhooks/infobip', $webhookPayload);
        
        // Should still return 200 (webhooks should not fail)
        $response->assertStatus(200);
        
        // Verify no logs were created
        $this->assertDatabaseMissing('email_campaign_logs', [
            'message_id' => 'non-existent-message-id',
        ]);
    }

    /**
     * Test multiple webhook events for same recipient
     */
    public function test_multiple_webhook_events_for_same_recipient(): void
    {
        // Create campaign and recipient
        [$campaign, $recipient] = $this->createCampaignWithRecipient();
        
        $messageId = 'infobip-msg-' . uniqid();
        $recipient->update([
            'status' => 'sent',
            'message_id' => $messageId,
            'sent_at' => now(),
        ]);
        
        // Event 1: Delivered
        $this->postJson('/event/webhooks/infobip', [
            'results' => [
                [
                    'messageId' => $messageId,
                    'to' => $recipient->email,
                    'status' => ['groupId' => 3, 'groupName' => 'DELIVERED'],
                ],
            ],
        ])->assertStatus(200);
        
        $recipient->refresh();
        $this->assertEquals('delivered', $recipient->status);
        
        // Event 2: Opened
        $this->postJson('/event/webhooks/infobip', [
            'results' => [
                [
                    'messageId' => $messageId,
                    'to' => $recipient->email,
                    'event' => 'OPEN',
                    'openedAt' => now()->toIso8601String(),
                ],
            ],
        ])->assertStatus(200);
        
        $recipient->refresh();
        $this->assertEquals('opened', $recipient->status);
        
        // Event 3: Clicked
        $this->postJson('/event/webhooks/infobip', [
            'results' => [
                [
                    'messageId' => $messageId,
                    'to' => $recipient->email,
                    'event' => 'CLICK',
                    'clickedAt' => now()->toIso8601String(),
                    'url' => 'https://example.com',
                ],
            ],
        ])->assertStatus(200);
        
        $recipient->refresh();
        $this->assertEquals('clicked', $recipient->status);
        
        // Verify all events logged
        $logs = EmailCampaignLog::where('message_id', $messageId)->get();
        $this->assertCount(3, $logs);
        $this->assertTrue($logs->pluck('event_type')->contains('delivered'));
        $this->assertTrue($logs->pluck('event_type')->contains('opened'));
        $this->assertTrue($logs->pluck('event_type')->contains('clicked'));
        
        // Verify campaign statistics
        $campaign->refresh();
        $this->assertEquals(1, $campaign->delivered_count);
        $this->assertEquals(1, $campaign->opened_count);
        $this->assertEquals(1, $campaign->clicked_count);
    }

    /**
     * Helper method to create a campaign with a recipient
     * 
     * @return array [EmailCampaign, EmailCampaignRecipient]
     */
    private function createCampaignWithRecipient(): array
    {
        // Create template
        $template = EmailTemplate::create([
            'name' => 'Webhook Test Template',
            'slug' => 'webhook-test-' . uniqid(),
            'category' => 'invitation',
            'subject' => 'Test Subject',
            'html_content' => '<p>Test content</p>',
            'text_content' => 'Test content',
            'is_active' => true,
        ]);
        
        // Create campaign
        $campaign = EmailCampaign::create([
            'email_template_id' => $template->id,
            'name' => 'Webhook Test Campaign',
            'sender_name' => 'Test Sender',
            'sender_email' => 'sender@test.com',
            'status' => 'sending',
            'recipient_source' => 'manual',
            'total_recipients' => 1,
            'started_at' => now(),
        ]);
        
        // Create recipient
        $recipient = EmailCampaignRecipient::create([
            'email_campaign_id' => $campaign->id,
            'email' => 'recipient@example.com',
            'first_name' => 'Test',
            'last_name' => 'Recipient',
            'merge_data' => [],
            'status' => 'pending',
        ]);
        
        return [$campaign, $recipient];
    }
}
