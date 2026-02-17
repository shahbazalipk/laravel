<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Persona;

class PersonaSeeder extends Seeder
{
    public function run(): void
    {
        $eventId = config('event.event_id');
        $orgId = config('event.org_id');
        
        $personas = [
            [
                'event_id' => $eventId,
                'org_id' => $orgId,
                'name' => 'Delegate',
                'slug' => 'delegate',
                'color' => '#3b82f6',
                'description' => 'Regular event attendee or delegate',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'event_id' => $eventId,
                'org_id' => $orgId,
                'name' => 'Visitor',
                'slug' => 'visitor',
                'color' => '#10b981',
                'description' => 'General visitor to the event',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'event_id' => $eventId,
                'org_id' => $orgId,
                'name' => 'Exhibitor',
                'slug' => 'exhibitor',
                'color' => '#f59e0b',
                'description' => 'Company or individual exhibiting at the event',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'event_id' => $eventId,
                'org_id' => $orgId,
                'name' => 'Speaker',
                'slug' => 'speaker',
                'color' => '#8b5cf6',
                'description' => 'Event speaker or presenter',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'event_id' => $eventId,
                'org_id' => $orgId,
                'name' => 'Sponsor',
                'slug' => 'sponsor',
                'color' => '#ec4899',
                'description' => 'Event sponsor representative',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'event_id' => $eventId,
                'org_id' => $orgId,
                'name' => 'VIP',
                'slug' => 'vip',
                'color' => '#eab308',
                'description' => 'VIP guest or special attendee',
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'event_id' => $eventId,
                'org_id' => $orgId,
                'name' => 'Media',
                'slug' => 'media',
                'color' => '#06b6d4',
                'description' => 'Press or media representative',
                'is_active' => true,
                'sort_order' => 7,
            ],
            [
                'event_id' => $eventId,
                'org_id' => $orgId,
                'name' => 'Staff',
                'slug' => 'staff',
                'color' => '#6b7280',
                'description' => 'Event staff or organizer',
                'is_active' => true,
                'sort_order' => 8,
            ],
        ];

        foreach ($personas as $persona) {
            Persona::create($persona);
        }
    }
}
