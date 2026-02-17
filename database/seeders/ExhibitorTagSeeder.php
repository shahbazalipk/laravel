<?php

namespace Database\Seeders;

use App\Models\ExhibitorTag;
use Illuminate\Database\Seeder;

class ExhibitorTagSeeder extends Seeder
{
    public function run(): void
    {
        $eventId = config('event.event_id');
        $orgId = config('event.org_id');

        $exhibitorTags = [
            [
                'name' => 'Featured Exhibitor',
                'slug' => 'featured-exhibitor',
                'color' => '#f59e0b',
                'description' => 'Premium featured exhibitors',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'New Exhibitor',
                'slug' => 'new-exhibitor',
                'color' => '#10b981',
                'description' => 'First-time exhibitors',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Startup',
                'slug' => 'startup',
                'color' => '#8b5cf6',
                'description' => 'Startup companies',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'International',
                'slug' => 'international',
                'color' => '#3b82f6',
                'description' => 'International exhibitors',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Local',
                'slug' => 'local',
                'color' => '#6366f1',
                'description' => 'Local exhibitors',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Innovation Award',
                'slug' => 'innovation-award',
                'color' => '#ef4444',
                'description' => 'Innovation award winners',
                'sort_order' => 6,
                'is_active' => true,
            ],
        ];

        foreach ($exhibitorTags as $exhibitorTag) {
            ExhibitorTag::create(array_merge($exhibitorTag, [
                'event_id' => $eventId,
                'org_id' => $orgId,
            ]));
        }
    }
}
