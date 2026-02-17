<?php

namespace Database\Seeders;

use App\Models\Industry;
use Illuminate\Database\Seeder;

class IndustrySeeder extends Seeder
{
    public function run(): void
    {
        $eventId = config('event.event_id');
        $orgId = config('event.org_id');

        $industries = [
            [
                'name' => 'Technology',
                'slug' => 'technology',
                'color' => '#3b82f6',
                'description' => 'Information technology and software',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Healthcare',
                'slug' => 'healthcare',
                'color' => '#ef4444',
                'description' => 'Healthcare and medical services',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Manufacturing',
                'slug' => 'manufacturing',
                'color' => '#f59e0b',
                'description' => 'Manufacturing and industrial',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Finance',
                'slug' => 'finance',
                'color' => '#10b981',
                'description' => 'Financial services and banking',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Education',
                'slug' => 'education',
                'color' => '#8b5cf6',
                'description' => 'Education and training',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Retail',
                'slug' => 'retail',
                'color' => '#ec4899',
                'description' => 'Retail and e-commerce',
                'sort_order' => 6,
                'is_active' => true,
            ],
            [
                'name' => 'Construction',
                'slug' => 'construction',
                'color' => '#f97316',
                'description' => 'Construction and real estate',
                'sort_order' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'Hospitality',
                'slug' => 'hospitality',
                'color' => '#06b6d4',
                'description' => 'Hospitality and tourism',
                'sort_order' => 8,
                'is_active' => true,
            ],
            [
                'name' => 'Transportation',
                'slug' => 'transportation',
                'color' => '#6366f1',
                'description' => 'Transportation and logistics',
                'sort_order' => 9,
                'is_active' => true,
            ],
            [
                'name' => 'Energy',
                'slug' => 'energy',
                'color' => '#eab308',
                'description' => 'Energy and utilities',
                'sort_order' => 10,
                'is_active' => true,
            ],
        ];

        foreach ($industries as $industry) {
            Industry::create(array_merge($industry, [
                'event_id' => $eventId,
                'org_id' => $orgId,
            ]));
        }
    }
}
