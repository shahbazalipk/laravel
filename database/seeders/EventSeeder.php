<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $eventId = config('event.event_id');
        $orgId = config('event.org_id');

        // Create or update the event with the configured IDs
        Event::updateOrCreate(
            [
                'id' => $eventId,
                'organization_id' => $orgId,
            ],
            [
                'event_id' => 'EVT-' . strtoupper(Str::random(10)),
                'title' => 'Laravel Event Manager Demo',
                'subdomain' => 'demo',
                'start_date' => now()->addMonths(2),
                'end_date' => now()->addMonths(2)->addDays(3),
                'timezone' => 'UTC',
                'type' => 'conference',
                'format' => 'hybrid',
                'country' => 'United States',
                'location' => 'Convention Center',
                'languages' => ['en'],
                'currency' => 'USD',
                'visibility' => 'public',
                'status' => 'published',
                'seo_title' => 'Laravel Event Manager Demo',
                'seo_description' => 'Professional event management platform built with Laravel',
                'event_name' => 'Laravel Event Manager Demo',
                'event_type' => 'Conference',
                'event_mode' => 'hybrid',
                'stage' => 'active',
                'online_reg_close' => now()->addMonths(2)->subDays(1),
                'vat_percentage' => 0,
                'tax_inclusive' => false,
                'registration_form_active' => true,
                'email_verification_required' => true,
                'code_verification_required' => false,
                'bulk_print_enabled' => true,
                'reprint_enabled' => true,
                'website_url' => 'https://example.com',
                'manager_name' => 'Event Manager',
                'manager_email' => 'manager@event.com',
                'manager_phone' => '+1234567890',
                'city' => 'New York',
                'state' => 'NY',
            ]
        );

        $this->command->info("Event created/updated with ID: {$eventId} for Organization: {$orgId}");
    }
}
