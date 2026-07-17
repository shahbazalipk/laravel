<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Event admin access is managed through organization_admin_users
     * and event_user assignments in the shared organization portal database.
     */
    public function run(): void
    {
        $this->command?->info('Skipped local admin seeding. Event admins authenticate via organization portal users assigned to this event.');
    }
}
