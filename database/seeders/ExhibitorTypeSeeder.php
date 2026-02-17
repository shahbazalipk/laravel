<?php

namespace Database\Seeders;

use App\Models\ExhibitorType;
use Illuminate\Database\Seeder;

class ExhibitorTypeSeeder extends Seeder
{
    public function run(): void
    {
        $eventId = config('event.event_id');
        $orgId = config('event.org_id');

        $exhibitorTypes = [
            [
                'name' => 'Manufacturer',
                'slug' => 'manufacturer',
                'color' => '#3b82f6',
                'description' => 'Product manufacturers',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Distributor',
                'slug' => 'distributor',
                'color' => '#10b981',
                'description' => 'Product distributors and wholesalers',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Service Provider',
                'slug' => 'service-provider',
                'color' => '#8b5cf6',
                'description' => 'Service-based companies',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Retailer',
                'slug' => 'retailer',
                'color' => '#f59e0b',
                'description' => 'Retail businesses',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Consultant',
                'slug' => 'consultant',
                'color' => '#6366f1',
                'description' => 'Consulting firms',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Association',
                'slug' => 'association',
                'color' => '#ef4444',
                'description' => 'Industry associations and organizations',
                'sort_order' => 6,
                'is_active' => true,
            ],
        ];

        foreach ($exhibitorTypes as $exhibitorType) {
            ExhibitorType::create(array_merge($exhibitorType, [
                'event_id' => $eventId,
                'org_id' => $orgId,
            ]));
        }
    }
}
