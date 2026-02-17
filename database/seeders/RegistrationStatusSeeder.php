<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RegistrationStatus;

class RegistrationStatusSeeder extends Seeder
{
    public function run(): void
    {
        $eventId = config('event.event_id');
        $orgId = config('event.org_id');
        
        $statuses = [
            [
                'event_id' => $eventId,
                'org_id' => $orgId,
                'name' => 'Pending',
                'slug' => 'pending',
                'color' => '#f59e0b',
                'description' => 'Registration is pending confirmation',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'event_id' => $eventId,
                'org_id' => $orgId,
                'name' => 'Confirmed',
                'slug' => 'confirmed',
                'color' => '#10b981',
                'description' => 'Registration has been confirmed',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'event_id' => $eventId,
                'org_id' => $orgId,
                'name' => 'Checked In',
                'slug' => 'checked-in',
                'color' => '#3b82f6',
                'description' => 'Attendee has checked in at the event',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'event_id' => $eventId,
                'org_id' => $orgId,
                'name' => 'Cancelled',
                'slug' => 'cancelled',
                'color' => '#ef4444',
                'description' => 'Registration has been cancelled',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'event_id' => $eventId,
                'org_id' => $orgId,
                'name' => 'Waitlisted',
                'slug' => 'waitlisted',
                'color' => '#8b5cf6',
                'description' => 'On waiting list for the event',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'event_id' => $eventId,
                'org_id' => $orgId,
                'name' => 'No Show',
                'slug' => 'no-show',
                'color' => '#6b7280',
                'description' => 'Registered but did not attend',
                'is_active' => true,
                'sort_order' => 6,
            ],
        ];

        foreach ($statuses as $status) {
            RegistrationStatus::create($status);
        }
    }
}
