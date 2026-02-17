<?php

namespace Tests\Feature;

use App\Models\EmailTemplate;
use App\Models\EmailCampaign;
use App\Models\EmailProviderConfig;
use App\Models\EmailUnsubscribe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Property 11: Event Scope Isolation
 * 
 * For any two different event_id values, creating templates/campaigns in one event 
 * should never be visible when querying from the other event context.
 * 
 * **Validates: Requirements (Multi-tenancy - implicit in all requirements)**
 */
class EventScopeIsolationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Property test: Event scope isolation for EmailTemplate
     * 
     * Tests that templates created in one event context are not visible
     * when querying from a different event context.
     */
    public function test_email_templates_are_isolated_by_event_scope(): void
    {
        // Run property test with multiple iterations
        $iterations = 100;
        
        for ($i = 0; $i < $iterations; $i++) {
            $uniqueId = uniqid() . '-' . $i;
            $this->runEventScopeIsolationTest(EmailTemplate::class, [
                'name' => "Template {$uniqueId}",
                'slug' => "template-{$uniqueId}",
                'category' => 'invitation',
                'subject' => 'Test Subject',
                'html_content' => '<p>Test content</p>',
                'text_content' => 'Test content',
                'is_active' => true,
            ]);
        }
    }

    /**
     * Property test: Event scope isolation for EmailCampaign
     * 
     * Tests that campaigns created in one event context are not visible
     * when querying from a different event context.
     */
    public function test_email_campaigns_are_isolated_by_event_scope(): void
    {
        // Run property test with multiple iterations
        $iterations = 100;
        
        for ($i = 0; $i < $iterations; $i++) {
            $uniqueId = uniqid() . '-' . $i;
            
            // First create a template in event 1
            config(['event.event_id' => 1, 'event.org_id' => 1]);
            $template = EmailTemplate::create([
                'name' => "Template for Campaign {$uniqueId}",
                'slug' => "template-campaign-{$uniqueId}",
                'category' => 'invitation',
                'subject' => 'Test Subject',
                'html_content' => '<p>Test content</p>',
                'text_content' => 'Test content',
                'is_active' => true,
            ]);
            
            $this->runEventScopeIsolationTest(EmailCampaign::class, [
                'email_template_id' => $template->id,
                'name' => "Campaign {$uniqueId}",
                'sender_name' => 'Test Sender',
                'sender_email' => 'sender@example.com',
                'status' => 'draft',
                'recipient_source' => 'registrations',
            ]);
            
            // Clean up template
            EmailTemplate::withoutGlobalScopes()->where('id', $template->id)->delete();
        }
    }

    /**
     * Property test: Event scope isolation for EmailProviderConfig
     * 
     * Tests that provider configs created in one event context are not visible
     * when querying from a different event context.
     */
    public function test_email_provider_configs_are_isolated_by_event_scope(): void
    {
        // Run property test with multiple iterations
        $iterations = 100;
        
        for ($i = 0; $i < $iterations; $i++) {
            $this->runEventScopeIsolationTest(EmailProviderConfig::class, [
                'provider_name' => 'smtp',
                'credentials' => [
                    'host' => 'smtp.example.com',
                    'port' => 587,
                    'username' => 'user',
                    'password' => 'pass',
                ],
                'settings' => ['rate_limit' => 100],
                'is_active' => true,
                'is_default' => false,
            ]);
        }
    }

    /**
     * Property test: Event scope isolation for EmailUnsubscribe
     * 
     * Tests that unsubscribes created in one event context are not visible
     * when querying from a different event context.
     */
    public function test_email_unsubscribes_are_isolated_by_event_scope(): void
    {
        // Run property test with multiple iterations
        $iterations = 100;
        
        for ($i = 0; $i < $iterations; $i++) {
            $this->runEventScopeIsolationTest(EmailUnsubscribe::class, [
                'email' => "user{$i}@example.com",
                'reason' => 'Not interested',
                'unsubscribed_at' => now(),
            ]);
        }
    }

    /**
     * Helper method to run event scope isolation test for any model
     * 
     * @param string $modelClass The model class to test
     * @param array $attributes The attributes to create the model with
     */
    private function runEventScopeIsolationTest(string $modelClass, array $attributes): void
    {
        // Generate two different event IDs
        $eventId1 = rand(1, 1000);
        $eventId2 = rand(1001, 2000);
        $orgId1 = rand(1, 1000);
        $orgId2 = rand(1001, 2000);
        
        // Ensure event IDs are different
        $this->assertNotEquals($eventId1, $eventId2);
        $this->assertNotEquals($orgId1, $orgId2);
        
        // Set context to event 1
        config(['event.event_id' => $eventId1, 'event.org_id' => $orgId1]);
        
        // Create record in event 1
        $recordInEvent1 = $modelClass::create($attributes);
        
        // Verify it was created with correct event_id and org_id
        $this->assertEquals($eventId1, $recordInEvent1->event_id);
        $this->assertEquals($orgId1, $recordInEvent1->org_id);
        
        // Verify it's visible in event 1 context
        $countInEvent1 = $modelClass::count();
        $this->assertGreaterThan(0, $countInEvent1);
        
        // Switch context to event 2
        config(['event.event_id' => $eventId2, 'event.org_id' => $orgId2]);
        
        // Verify record is NOT visible in event 2 context
        $countInEvent2 = $modelClass::count();
        $this->assertEquals(0, $countInEvent2, 
            "Records from event {$eventId1} should not be visible in event {$eventId2} context");
        
        // Verify we cannot find the record by ID in event 2 context
        $foundRecord = $modelClass::find($recordInEvent1->id);
        $this->assertNull($foundRecord, 
            "Record from event {$eventId1} should not be findable in event {$eventId2} context");
        
        // Create a record in event 2 with unique attributes if slug exists
        $attributesEvent2 = $attributes;
        if (isset($attributesEvent2['slug'])) {
            $attributesEvent2['slug'] = $attributesEvent2['slug'] . '-event2';
        }
        if (isset($attributesEvent2['name'])) {
            $attributesEvent2['name'] = $attributesEvent2['name'] . ' Event2';
        }
        
        $recordInEvent2 = $modelClass::create($attributesEvent2);
        
        // Verify it was created with correct event_id and org_id
        $this->assertEquals($eventId2, $recordInEvent2->event_id);
        $this->assertEquals($orgId2, $recordInEvent2->org_id);
        
        // Verify only event 2 record is visible in event 2 context
        $countInEvent2After = $modelClass::count();
        $this->assertGreaterThan(0, $countInEvent2After);
        
        // Switch back to event 1
        config(['event.event_id' => $eventId1, 'event.org_id' => $orgId1]);
        
        // Verify event 2 record is NOT visible in event 1 context
        $foundEvent2Record = $modelClass::find($recordInEvent2->id);
        $this->assertNull($foundEvent2Record, 
            "Record from event {$eventId2} should not be findable in event {$eventId1} context");
        
        // Verify only event 1 records are visible
        $finalCountInEvent1 = $modelClass::count();
        $this->assertEquals($countInEvent1, $finalCountInEvent1, 
            "Event 1 should only see its own records");
        
        // Clean up - use withoutGlobalScopes to delete across events
        $modelClass::withoutGlobalScopes()->where('id', $recordInEvent1->id)->delete();
        $modelClass::withoutGlobalScopes()->where('id', $recordInEvent2->id)->delete();
    }

    /**
     * Property test: Cross-organization isolation within same event
     * 
     * Tests that records from different organizations within the same event
     * are properly isolated.
     */
    public function test_records_are_isolated_by_organization_within_same_event(): void
    {
        $iterations = 50;
        
        for ($i = 0; $i < $iterations; $i++) {
            $uniqueId = uniqid() . '-' . $i;
            $eventId = rand(1, 1000);
            $orgId1 = rand(1, 1000);
            $orgId2 = rand(1001, 2000);
            
            // Ensure org IDs are different
            $this->assertNotEquals($orgId1, $orgId2);
            
            // Set context to org 1
            config(['event.event_id' => $eventId, 'event.org_id' => $orgId1]);
            
            // Create template in org 1
            $templateOrg1 = EmailTemplate::create([
                'name' => "Template Org1 {$uniqueId}",
                'slug' => "template-org1-{$uniqueId}",
                'category' => 'invitation',
                'subject' => 'Test Subject',
                'html_content' => '<p>Test content</p>',
                'text_content' => 'Test content',
                'is_active' => true,
            ]);
            
            $this->assertEquals($eventId, $templateOrg1->event_id);
            $this->assertEquals($orgId1, $templateOrg1->org_id);
            
            // Switch to org 2 (same event)
            config(['event.event_id' => $eventId, 'event.org_id' => $orgId2]);
            
            // Verify org 1 template is NOT visible
            $foundTemplate = EmailTemplate::find($templateOrg1->id);
            $this->assertNull($foundTemplate, 
                "Template from org {$orgId1} should not be visible in org {$orgId2} context");
            
            // Create template in org 2
            $templateOrg2 = EmailTemplate::create([
                'name' => "Template Org2 {$uniqueId}",
                'slug' => "template-org2-{$uniqueId}",
                'category' => 'invitation',
                'subject' => 'Test Subject',
                'html_content' => '<p>Test content</p>',
                'text_content' => 'Test content',
                'is_active' => true,
            ]);
            
            $this->assertEquals($eventId, $templateOrg2->event_id);
            $this->assertEquals($orgId2, $templateOrg2->org_id);
            
            // Verify only org 2 template is visible
            $count = EmailTemplate::count();
            $this->assertGreaterThan(0, $count);
            
            // Switch back to org 1
            config(['event.event_id' => $eventId, 'event.org_id' => $orgId1]);
            
            // Verify org 2 template is NOT visible
            $foundOrg2Template = EmailTemplate::find($templateOrg2->id);
            $this->assertNull($foundOrg2Template, 
                "Template from org {$orgId2} should not be visible in org {$orgId1} context");
            
            // Clean up
            EmailTemplate::withoutGlobalScopes()->where('id', $templateOrg1->id)->delete();
            EmailTemplate::withoutGlobalScopes()->where('id', $templateOrg2->id)->delete();
        }
    }
}
