<?php

namespace Database\Seeders;

use App\Models\ProductType;
use Illuminate\Database\Seeder;

class ProductTypeSeeder extends Seeder
{
    public function run(): void
    {
        $eventId = config('event.event_id');
        $orgId = config('event.org_id');

        $productTypes = [
            [
                'name' => 'Electronics',
                'slug' => 'electronics',
                'color' => '#3b82f6',
                'description' => 'Electronic devices and components',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Software',
                'slug' => 'software',
                'color' => '#8b5cf6',
                'description' => 'Software solutions and applications',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Hardware',
                'slug' => 'hardware',
                'color' => '#ef4444',
                'description' => 'Computer hardware and peripherals',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Services',
                'slug' => 'services',
                'color' => '#10b981',
                'description' => 'Professional services and consulting',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Medical Equipment',
                'slug' => 'medical-equipment',
                'color' => '#f59e0b',
                'description' => 'Medical devices and healthcare equipment',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Industrial',
                'slug' => 'industrial',
                'color' => '#6366f1',
                'description' => 'Industrial machinery and equipment',
                'sort_order' => 6,
                'is_active' => true,
            ],
        ];

        foreach ($productTypes as $productType) {
            ProductType::create(array_merge($productType, [
                'event_id' => $eventId,
                'org_id' => $orgId,
            ]));
        }
    }
}
