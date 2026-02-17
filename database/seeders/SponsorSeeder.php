<?php

namespace Database\Seeders;

use App\Models\Sponsor;
use Illuminate\Database\Seeder;

class SponsorSeeder extends Seeder
{
    public function run(): void
    {
        $eventId = config('event.event_id');
        $orgId = config('event.org_id');

        $sponsors = [
            [
                'name' => 'Tech Innovations Inc.',
                'sponsorship_label' => 'Platinum Sponsor',
                'type' => 'Platinum',
                'description' => 'Leading technology solutions provider specializing in enterprise software and cloud infrastructure.',
                'website_url' => 'https://techinnovations.example.com',
                'contact_email' => 'partnerships@techinnovations.example.com',
                'contact_phone' => '+1 (555) 123-4567',
                'visible_online' => true,
                'visible_onsite' => true,
                'visible_on_ebadge' => true,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Global Finance Corp',
                'sponsorship_label' => 'Gold Sponsor',
                'type' => 'Gold',
                'description' => 'International financial services company providing innovative banking solutions.',
                'website_url' => 'https://globalfinance.example.com',
                'contact_email' => 'events@globalfinance.example.com',
                'contact_phone' => '+1 (555) 234-5678',
                'visible_online' => true,
                'visible_onsite' => true,
                'visible_on_exhibitor_portal' => true,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Digital Marketing Pro',
                'sponsorship_label' => 'Silver Sponsor',
                'type' => 'Silver',
                'description' => 'Full-service digital marketing agency helping businesses grow their online presence.',
                'website_url' => 'https://digitalmarketingpro.example.com',
                'contact_email' => 'hello@digitalmarketingpro.example.com',
                'visible_online' => true,
                'visible_onsite' => true,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Cloud Solutions Ltd',
                'sponsorship_label' => 'Bronze Sponsor',
                'type' => 'Bronze',
                'description' => 'Cloud infrastructure and hosting services for modern businesses.',
                'website_url' => 'https://cloudsolutions.example.com',
                'contact_email' => 'support@cloudsolutions.example.com',
                'contact_phone' => '+1 (555) 345-6789',
                'visible_online' => true,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Innovation Labs',
                'sponsorship_label' => 'Supporting Sponsor',
                'type' => 'Supporting',
                'description' => 'Research and development company focused on emerging technologies.',
                'website_url' => 'https://innovationlabs.example.com',
                'visible_online' => true,
                'visible_on_group_portal' => true,
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($sponsors as $sponsorData) {
            Sponsor::create(array_merge($sponsorData, [
                'event_id' => $eventId,
                'org_id' => $orgId,
            ]));
        }
    }
}
