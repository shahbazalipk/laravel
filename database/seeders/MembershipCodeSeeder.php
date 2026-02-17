<?php

namespace Database\Seeders;

use App\Models\Membership;
use App\Models\MembershipCode;
use Illuminate\Database\Seeder;

class MembershipCodeSeeder extends Seeder
{
    public function run(): void
    {
        $eventId = config('event.event_id');
        $orgId = config('event.org_id');

        // Get first membership (IEEE)
        $membership = Membership::where('event_id', $eventId)
            ->where('org_id', $orgId)
            ->where('slug', 'ieee-membership')
            ->first();

        if ($membership) {
            $codes = [
                ['code' => '909876542', 'allowed_usage' => 5, 'used' => 0, 'status' => 'active'],
                ['code' => '897894562', 'allowed_usage' => 6, 'used' => 0, 'status' => 'active'],
                ['code' => 'qwertytest1113', 'allowed_usage' => 2, 'used' => 1, 'status' => 'active'],
                ['code' => 'qwertytest2', 'allowed_usage' => 1, 'used' => 0, 'status' => 'active'],
                ['code' => 'index111qwerty', 'allowed_usage' => 5, 'used' => 6, 'status' => 'inactive'],
            ];

            foreach ($codes as $codeData) {
                MembershipCode::create(array_merge($codeData, [
                    'membership_id' => $membership->id,
                    'event_id' => $eventId,
                    'org_id' => $orgId,
                ]));
            }
        }
    }
}
