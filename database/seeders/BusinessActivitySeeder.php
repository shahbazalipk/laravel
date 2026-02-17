<?php

namespace Database\Seeders;

use App\Models\BusinessActivity;
use Illuminate\Database\Seeder;

class BusinessActivitySeeder extends Seeder
{
    public function run(): void
    {
        $eventId = config('event.event_id');
        $orgId = config('event.org_id');

        $businessActivities = [
            [
                'name' => 'Import',
                'slug' => 'import',
                'color' => '#3b82f6',
                'description' => 'Importing goods and services',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Export',
                'slug' => 'export',
                'color' => '#10b981',
                'description' => 'Exporting goods and services',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Manufacturing',
                'slug' => 'manufacturing',
                'color' => '#f59e0b',
                'description' => 'Manufacturing and production',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Distribution',
                'slug' => 'distribution',
                'color' => '#8b5cf6',
                'description' => 'Distribution and wholesale',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Retail',
                'slug' => 'retail',
                'color' => '#ec4899',
                'description' => 'Retail sales',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Consulting',
                'slug' => 'consulting',
                'color' => '#6366f1',
                'description' => 'Consulting and advisory services',
                'sort_order' => 6,
                'is_active' => true,
            ],
            [
                'name' => 'Research & Development',
                'slug' => 'research-development',
                'color' => '#ef4444',
                'description' => 'Research and development activities',
                'sort_order' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'Marketing',
                'slug' => 'marketing',
                'color' => '#06b6d4',
                'description' => 'Marketing and advertising',
                'sort_order' => 8,
                'is_active' => true,
            ],
        ];

        foreach ($businessActivities as $businessActivity) {
            BusinessActivity::create(array_merge($businessActivity, [
                'event_id' => $eventId,
                'org_id' => $orgId,
            ]));
        }
    }
}
