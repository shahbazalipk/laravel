<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Exhibitor;
use App\Models\ExhibitorType;
use App\Models\Industry;
use App\Models\BoothType;
use App\Models\BusinessActivity;
use App\Models\ProductType;
use App\Models\ExhibitorTag;

class ExhibitorSeeder extends Seeder
{
    public function run(): void
    {
        $eventId = config('event.event_id');
        $orgId = config('event.org_id');

        // Get related data
        $exhibitorTypes = ExhibitorType::all();
        $industries = Industry::all();
        $boothTypes = BoothType::all();
        $businessActivities = BusinessActivity::all();
        $productTypes = ProductType::all();
        $tags = ExhibitorTag::all();

        if ($exhibitorTypes->isEmpty() || $industries->isEmpty()) {
            $this->command->warn('Please run ExhibitorType and Industry seeders first');
            return;
        }

        $exhibitors = [
            [
                'company_name' => 'TechVision Solutions',
                'exhibitor_type_id' => $exhibitorTypes->where('name', 'Manufacturer')->first()?->id ?? $exhibitorTypes->first()->id,
                'industry_id' => $industries->where('name', 'Technology')->first()?->id ?? $industries->first()->id,
                'contact_person_name' => 'John Smith',
                'contact_email' => 'john.smith@techvision.com',
                'contact_phone' => '+1 555-0101',
                'description' => 'Leading provider of innovative technology solutions for businesses worldwide.',
                'website_url' => 'https://techvision.example.com',
                'year_established' => 2010,
                'company_size' => '100-500 employees',
                'booth_type_id' => $boothTypes->where('name', 'Premium')->first()?->id,
                'booth_number' => 'A-101',
                'booth_size' => 18.00,
                'city' => 'San Francisco',
                'state' => 'California',
                'country' => 'USA',
                'visible_on_website' => true,
                'visible_on_app' => true,
                'visible_in_directory' => true,
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'company_name' => 'Global Healthcare Inc',
                'exhibitor_type_id' => $exhibitorTypes->where('name', 'Distributor')->first()?->id ?? $exhibitorTypes->first()->id,
                'industry_id' => $industries->where('name', 'Healthcare')->first()?->id ?? $industries->first()->id,
                'contact_person_name' => 'Sarah Johnson',
                'contact_email' => 'sarah.j@globalhealthcare.com',
                'contact_phone' => '+1 555-0102',
                'description' => 'Comprehensive healthcare solutions and medical equipment distribution.',
                'website_url' => 'https://globalhealthcare.example.com',
                'year_established' => 2005,
                'company_size' => '500-1000 employees',
                'booth_type_id' => $boothTypes->where('name', 'Island')->first()?->id,
                'booth_number' => 'B-205',
                'booth_size' => 36.00,
                'city' => 'Boston',
                'state' => 'Massachusetts',
                'country' => 'USA',
                'visible_on_website' => true,
                'visible_on_app' => true,
                'visible_in_directory' => true,
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'company_name' => 'EcoManufacturing Ltd',
                'exhibitor_type_id' => $exhibitorTypes->where('name', 'Manufacturer')->first()?->id ?? $exhibitorTypes->first()->id,
                'industry_id' => $industries->where('name', 'Manufacturing')->first()?->id ?? $industries->first()->id,
                'contact_person_name' => 'Michael Chen',
                'contact_email' => 'mchen@ecomanufacturing.com',
                'contact_phone' => '+1 555-0103',
                'description' => 'Sustainable manufacturing solutions with eco-friendly practices.',
                'website_url' => 'https://ecomanufacturing.example.com',
                'year_established' => 2015,
                'company_size' => '50-100 employees',
                'booth_type_id' => $boothTypes->where('name', 'Standard')->first()?->id,
                'booth_number' => 'C-310',
                'booth_size' => 9.00,
                'city' => 'Portland',
                'state' => 'Oregon',
                'country' => 'USA',
                'visible_on_website' => true,
                'visible_on_app' => true,
                'visible_in_directory' => true,
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($exhibitors as $exhibitorData) {
            $exhibitorData['event_id'] = $eventId;
            $exhibitorData['org_id'] = $orgId;

            $exhibitor = Exhibitor::create($exhibitorData);

            // Attach random business activities (1-3)
            if ($businessActivities->isNotEmpty()) {
                $exhibitor->businessActivities()->attach(
                    $businessActivities->random(min(3, $businessActivities->count()))->pluck('id')
                );
            }

            // Attach random product types (1-4)
            if ($productTypes->isNotEmpty()) {
                $exhibitor->productTypes()->attach(
                    $productTypes->random(min(4, $productTypes->count()))->pluck('id')
                );
            }

            // Attach random tags (1-3)
            if ($tags->isNotEmpty()) {
                $exhibitor->tags()->attach(
                    $tags->random(min(3, $tags->count()))->pluck('id')
                );
            }
        }

        $this->command->info('Exhibitors seeded successfully!');
    }
}
