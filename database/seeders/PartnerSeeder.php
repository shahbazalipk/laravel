<?php

namespace Database\Seeders;

use App\Models\Partner;
use Illuminate\Database\Seeder;

class PartnerSeeder extends Seeder
{
    public function run(): void
    {
        $eventId = config('event.event_id');
        $orgId = config('event.org_id');

        $partners = [
            [
                'name' => 'Tech Media Network',
                'sponsorship_label' => 'Media Partner',
                'type' => 'Media',
                'description' => 'Leading technology news and media platform covering industry trends and innovations.',
                'website_url' => 'https://techmedianetwork.example.com',
                'contact_email' => 'partnerships@techmedianetwork.example.com',
                'contact_phone' => '+1 (555) 456-7890',
                'visible_online' => true,
                'visible_onsite' => true,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'University of Technology',
                'sponsorship_label' => 'Academic Partner',
                'type' => 'Academic',
                'description' => 'Premier institution for technology education and research.',
                'website_url' => 'https://utech.example.edu',
                'contact_email' => 'events@utech.example.edu',
                'visible_online' => true,
                'visible_onsite' => true,
                'visible_on_group_portal' => true,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Developer Community Hub',
                'sponsorship_label' => 'Community Partner',
                'type' => 'Community',
                'description' => 'Global community of developers sharing knowledge and best practices.',
                'website_url' => 'https://devcommunityhub.example.com',
                'contact_email' => 'hello@devcommunityhub.example.com',
                'visible_online' => true,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Strategic Consulting Group',
                'sponsorship_label' => 'Strategic Partner',
                'type' => 'Strategic',
                'description' => 'Business strategy and consulting firm helping organizations achieve their goals.',
                'website_url' => 'https://strategicconsulting.example.com',
                'contact_email' => 'info@strategicconsulting.example.com',
                'contact_phone' => '+1 (555) 567-8901',
                'visible_online' => true,
                'visible_onsite' => true,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Tech Association',
                'sponsorship_label' => 'Association Partner',
                'type' => 'Association',
                'description' => 'Professional association representing technology industry professionals.',
                'website_url' => 'https://techassociation.example.org',
                'contact_email' => 'contact@techassociation.example.org',
                'visible_online' => true,
                'visible_on_ebadge' => true,
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($partners as $partnerData) {
            Partner::create(array_merge($partnerData, [
                'event_id' => $eventId,
                'org_id' => $orgId,
            ]));
        }
    }
}
