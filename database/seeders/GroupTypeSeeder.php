<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GroupType;

class GroupTypeSeeder extends Seeder
{
    public function run(): void
    {
        $eventId = config('event.event_id');
        $orgId = config('event.org_id');

        $groupTypes = [
            [
                'name' => 'Corporate',
                'slug' => 'corporate',
                'description' => 'Corporate group registrations',
                'color' => '#3b82f6',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Academic',
                'slug' => 'academic',
                'description' => 'Academic institutions and universities',
                'color' => '#10b981',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Association',
                'slug' => 'association',
                'description' => 'Professional associations and societies',
                'color' => '#f59e0b',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Government',
                'slug' => 'government',
                'description' => 'Government agencies and departments',
                'color' => '#8b5cf6',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Non-Profit',
                'slug' => 'non-profit',
                'description' => 'Non-profit organizations',
                'color' => '#ec4899',
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($groupTypes as $type) {
            GroupType::create(array_merge($type, [
                'event_id' => $eventId,
                'org_id' => $orgId,
            ]));
        }
    }
}
