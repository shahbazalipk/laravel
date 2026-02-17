<?php

namespace Database\Seeders;

use App\Models\CategoryType;
use Illuminate\Database\Seeder;

class CategoryTypeSeeder extends Seeder
{
    public function run(): void
    {
        $eventId = env('EVENT_ID');
        $orgId = env('ORG_ID');

        $categoryTypes = [
            [
                'name' => 'Early Bird',
                'slug' => 'early-bird',
                'color' => '#10b981',
                'description' => 'Special discounted rate for early registrations',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Standard',
                'slug' => 'standard',
                'color' => '#6366f1',
                'description' => 'Regular registration rate',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'VIP',
                'slug' => 'vip',
                'color' => '#f59e0b',
                'description' => 'Premium access with exclusive benefits',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Group',
                'slug' => 'group',
                'color' => '#8b5cf6',
                'description' => 'Special rate for group registrations',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Student',
                'slug' => 'student',
                'color' => '#3b82f6',
                'description' => 'Discounted rate for students',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Corporate',
                'slug' => 'corporate',
                'color' => '#ef4444',
                'description' => 'Corporate registration package',
                'sort_order' => 6,
                'is_active' => true,
            ],
        ];

        foreach ($categoryTypes as $typeData) {
            CategoryType::create(array_merge($typeData, [
                'event_id' => $eventId,
                'org_id' => $orgId,
            ]));
        }

        $this->command->info('Category Types seeded successfully!');
    }
}
