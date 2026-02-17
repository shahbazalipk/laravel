<?php

namespace Database\Seeders;

use App\Models\BoothType;
use Illuminate\Database\Seeder;

class BoothTypeSeeder extends Seeder
{
    public function run(): void
    {
        $eventId = config('event.event_id');
        $orgId = config('event.org_id');

        $boothTypes = [
            [
                'name' => 'Standard Booth',
                'slug' => 'standard-booth',
                'color' => '#3b82f6',
                'description' => 'Standard exhibition booth space',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Premium Booth',
                'slug' => 'premium-booth',
                'color' => '#f59e0b',
                'description' => 'Premium booth with enhanced features',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Corner Booth',
                'slug' => 'corner-booth',
                'color' => '#8b5cf6',
                'description' => 'Corner booth with two open sides',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Island Booth',
                'slug' => 'island-booth',
                'color' => '#ef4444',
                'description' => 'Island booth with four open sides',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Shell Scheme',
                'slug' => 'shell-scheme',
                'color' => '#10b981',
                'description' => 'Pre-built shell scheme booth',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Custom Built',
                'slug' => 'custom-built',
                'color' => '#6366f1',
                'description' => 'Custom designed and built booth',
                'sort_order' => 6,
                'is_active' => true,
            ],
        ];

        foreach ($boothTypes as $boothType) {
            BoothType::create(array_merge($boothType, [
                'event_id' => $eventId,
                'org_id' => $orgId,
            ]));
        }
    }
}
