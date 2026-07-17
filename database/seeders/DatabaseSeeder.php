<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            EventSeeder::class, // Must run first to create the event
            AdminSeeder::class,
            RegistrationStatusSeeder::class,
            PersonaSeeder::class,
            MembershipSeeder::class,
            CategoryTypeSeeder::class,
            RegistrationCategorySeeder::class,
            EmailTemplateSeeder::class,
            LandingPageTemplateSeeder::class, // Landing page templates
            SalesDemoSeeder::class, // Sales pipeline type templates (local/dev/testing only)
            // EventDataSeeder::class, // Skipped - needs agenda system update
            // FileSeeder::class, // Skipped - optional
            // SponsorSeeder::class, // Skipped - optional
            // PartnerSeeder::class, // Skipped - optional
            // AgendaSeeder::class, // Skipped - optional
        ]);
    }
}
