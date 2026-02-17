<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Group;
use App\Models\GroupType;
use App\Models\Industry;

class GroupSeeder extends Seeder
{
    public function run(): void
    {
        $eventId = config('event.event_id');
        $orgId = config('event.org_id');

        $groupType = GroupType::where('slug', 'corporate')->first();
        $industry = Industry::first();

        $groups = [
            [
                'group_name' => 'Tech Innovators Alliance',
                'group_type_id' => $groupType?->id,
                'organization_name' => 'Tech Innovators Inc.',
                'industry_id' => $industry?->id,
                'description' => 'A group of technology professionals attending the conference together',
                'website_url' => 'https://techinnovators.example.com',
                'allowed_attendees' => 25,
                'invoice_number' => 'INV-2026-001',
                'primary_contact_name' => 'John Smith',
                'primary_contact_email' => 'john.smith@techinnovators.example.com',
                'primary_contact_phone' => '+1-555-0101',
                'secondary_contact_name' => 'Jane Doe',
                'secondary_contact_email' => 'jane.doe@techinnovators.example.com',
                'secondary_contact_phone' => '+1-555-0102',
                'address' => '123 Innovation Drive',
                'city' => 'San Francisco',
                'state' => 'CA',
                'postal_code' => '94105',
                'country' => 'United States',
                'special_requirements' => 'Vegetarian meal options for 5 attendees, wheelchair accessibility needed',
                'is_active' => true,
                'is_vip' => true,
                'sort_order' => 1,
            ],
            [
                'group_name' => 'Healthcare Professionals Network',
                'group_type_id' => $groupType?->id,
                'organization_name' => 'MedCare Solutions',
                'industry_id' => $industry?->id,
                'description' => 'Healthcare professionals from various departments',
                'website_url' => 'https://medcare.example.com',
                'allowed_attendees' => 15,
                'invoice_number' => 'INV-2026-002',
                'primary_contact_name' => 'Dr. Sarah Johnson',
                'primary_contact_email' => 'sarah.johnson@medcare.example.com',
                'primary_contact_phone' => '+1-555-0201',
                'address' => '456 Medical Plaza',
                'city' => 'Boston',
                'state' => 'MA',
                'postal_code' => '02108',
                'country' => 'United States',
                'special_requirements' => 'Early check-in requested',
                'is_active' => true,
                'is_vip' => false,
                'sort_order' => 2,
            ],
            [
                'group_name' => 'Finance Leaders Forum',
                'group_type_id' => $groupType?->id,
                'organization_name' => 'Global Finance Corp',
                'industry_id' => $industry?->id,
                'description' => 'Senior finance executives attending together',
                'website_url' => 'https://globalfinance.example.com',
                'allowed_attendees' => 10,
                'invoice_number' => 'INV-2026-003',
                'primary_contact_name' => 'Michael Chen',
                'primary_contact_email' => 'michael.chen@globalfinance.example.com',
                'primary_contact_phone' => '+1-555-0301',
                'address' => '789 Wall Street',
                'city' => 'New York',
                'state' => 'NY',
                'postal_code' => '10005',
                'country' => 'United States',
                'is_active' => true,
                'is_vip' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($groups as $group) {
            Group::create(array_merge($group, [
                'event_id' => $eventId,
                'org_id' => $orgId,
            ]));
        }
    }
}
