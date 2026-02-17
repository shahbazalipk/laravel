<?php

namespace Database\Seeders;

use App\Models\Membership;
use Illuminate\Database\Seeder;

class MembershipSeeder extends Seeder
{
    public function run(): void
    {
        $eventId = config('event.event_id');
        $orgId = config('event.org_id');

        $memberships = [
            [
                'name' => 'IEEE Membership',
                'slug' => 'ieee-membership',
                'verification_type' => 'third_party_api',
                'api_endpoint' => 'https://api.ieee.org/verify',
                'api_key' => 'sample_api_key_here',
                'api_headers' => json_encode(['Accept' => 'application/json']),
                'color' => '#0066cc',
                'description' => 'IEEE (Institute of Electrical and Electronics Engineers) membership verification',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'ACM Membership',
                'slug' => 'acm-membership',
                'verification_type' => 'third_party_api',
                'api_endpoint' => 'https://api.acm.org/verify',
                'api_key' => 'sample_api_key_here',
                'api_headers' => json_encode(['Accept' => 'application/json']),
                'color' => '#0085ca',
                'description' => 'ACM (Association for Computing Machinery) membership verification',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Professional Engineers',
                'slug' => 'professional-engineers',
                'verification_type' => 'upload_file',
                'file_path' => null,
                'color' => '#28a745',
                'description' => 'Professional Engineers membership verification via uploaded file',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Student Association',
                'slug' => 'student-association',
                'verification_type' => 'upload_file',
                'file_path' => null,
                'color' => '#ffc107',
                'description' => 'Student association membership verification via uploaded file',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Industry Partners',
                'slug' => 'industry-partners',
                'verification_type' => 'third_party_api',
                'api_endpoint' => 'https://api.partners.org/verify',
                'api_key' => 'sample_api_key_here',
                'api_headers' => json_encode(['Accept' => 'application/json', 'Content-Type' => 'application/json']),
                'color' => '#6f42c1',
                'description' => 'Industry partners membership verification',
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($memberships as $membership) {
            Membership::create(array_merge($membership, [
                'event_id' => $eventId,
                'org_id' => $orgId,
            ]));
        }
    }
}
