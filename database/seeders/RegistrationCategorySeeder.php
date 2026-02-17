<?php

namespace Database\Seeders;

use App\Models\RegistrationCategory;
use App\Models\Persona;
use App\Models\CategoryType;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class RegistrationCategorySeeder extends Seeder
{
    public function run(): void
    {
        $eventId = env('EVENT_ID');
        $orgId = env('ORG_ID');

        // Get first persona for mobile persona (fallback if none exists)
        $defaultPersona = Persona::first();
        
        if (!$defaultPersona) {
            $this->command->warn('No personas found. Please run PersonaSeeder first.');
            return;
        }

        $categories = [
            [
                'name' => 'Conference Pass - Full Access',
                'mobile_persona_id' => $defaultPersona->id,
                'mobile_persona_color' => '#6366f1',
                'badge_name' => 'Full Access',
                'instructions_text' => 'This pass grants you full access to all conference sessions, workshops, and networking events.',
                'instruction_description' => 'Please arrive 30 minutes early for badge pickup. Bring a valid ID for verification.',
                'valid_from' => Carbon::now()->addDays(7),
                'valid_to' => Carbon::now()->addMonths(3),
                'price' => 500.00,
                'currency' => 'AED',
                'vat_percentage' => 5.00,
                'show_trn' => true,
                'visible' => true,
                'minimum_items' => 0,
                'maximum_items' => 10,
                'minimum_options' => 0,
                'maximum_options' => 5,
                'need_professional_student_id' => false,
                'need_membership_id' => false,
                'needs_password' => false,
                'sponsored' => false,
                'send_to_dtcm' => true,
                'capacity' => 500,
                'color' => '#6366f1',
                'description' => 'Complete access to all conference activities including keynotes, breakout sessions, and networking events.',
                'sort_order' => 1,
                'is_active' => true,
                'category_types' => ['early-bird', 'standard', 'vip'],
            ],
            [
                'name' => 'Workshop Only Pass',
                'mobile_persona_id' => $defaultPersona->id,
                'mobile_persona_color' => '#8b5cf6',
                'badge_name' => 'Workshop',
                'instructions_text' => 'Access to selected workshops only. Does not include main conference sessions.',
                'instruction_description' => 'Workshop materials will be provided. Please bring your laptop for hands-on sessions.',
                'valid_from' => Carbon::now()->addDays(7),
                'valid_to' => Carbon::now()->addMonths(3),
                'price' => 200.00,
                'currency' => 'AED',
                'vat_percentage' => 5.00,
                'show_trn' => true,
                'visible' => true,
                'minimum_items' => 0,
                'maximum_items' => 3,
                'minimum_options' => 0,
                'maximum_options' => 2,
                'need_professional_student_id' => false,
                'need_membership_id' => false,
                'needs_password' => false,
                'sponsored' => false,
                'send_to_dtcm' => false,
                'capacity' => 100,
                'color' => '#8b5cf6',
                'description' => 'Specialized workshops with industry experts. Limited to 3 workshops per registration.',
                'sort_order' => 2,
                'is_active' => true,
                'category_types' => ['standard', 'student'],
            ],
            [
                'name' => 'Virtual Attendance',
                'mobile_persona_id' => $defaultPersona->id,
                'mobile_persona_color' => '#3b82f6',
                'badge_name' => 'Virtual',
                'instructions_text' => 'Join us online! Access all sessions via live stream.',
                'instruction_description' => 'You will receive login credentials 24 hours before the event. Ensure stable internet connection.',
                'valid_from' => Carbon::now()->addDays(7),
                'valid_to' => Carbon::now()->addMonths(3),
                'price' => 150.00,
                'currency' => 'AED',
                'vat_percentage' => 5.00,
                'show_trn' => false,
                'visible' => true,
                'minimum_items' => 0,
                'maximum_items' => null,
                'minimum_options' => 0,
                'maximum_options' => null,
                'need_professional_student_id' => false,
                'need_membership_id' => false,
                'needs_password' => false,
                'sponsored' => false,
                'send_to_dtcm' => false,
                'capacity' => null,
                'color' => '#3b82f6',
                'description' => 'Attend virtually from anywhere in the world. Includes access to recorded sessions for 30 days.',
                'sort_order' => 3,
                'is_active' => true,
                'category_types' => ['early-bird', 'standard'],
            ],
            [
                'name' => 'Student Pass',
                'mobile_persona_id' => $defaultPersona->id,
                'mobile_persona_color' => '#10b981',
                'badge_name' => 'Student',
                'instructions_text' => 'Special discounted rate for students. Valid student ID required.',
                'instruction_description' => 'You must present a valid student ID at registration desk for verification.',
                'valid_from' => Carbon::now()->addDays(7),
                'valid_to' => Carbon::now()->addMonths(3),
                'price' => 100.00,
                'currency' => 'AED',
                'vat_percentage' => 5.00,
                'show_trn' => false,
                'visible' => true,
                'minimum_items' => 0,
                'maximum_items' => 5,
                'minimum_options' => 0,
                'maximum_options' => 3,
                'need_professional_student_id' => true,
                'professional_student_id_message' => 'Please upload a clear photo of your valid student ID card.',
                'need_membership_id' => false,
                'needs_password' => false,
                'sponsored' => false,
                'send_to_dtcm' => true,
                'capacity' => 200,
                'color' => '#10b981',
                'description' => 'Exclusive student pricing with full conference access. Student verification required.',
                'sort_order' => 4,
                'is_active' => true,
                'category_types' => ['student'],
            ],
            [
                'name' => 'Corporate Package',
                'mobile_persona_id' => $defaultPersona->id,
                'mobile_persona_color' => '#ef4444',
                'badge_name' => 'Corporate',
                'instructions_text' => 'Bulk registration for corporate teams. Minimum 5 attendees.',
                'instruction_description' => 'Contact our corporate sales team for customized packages and additional benefits.',
                'valid_from' => Carbon::now()->addDays(7),
                'valid_to' => Carbon::now()->addMonths(3),
                'price' => 2000.00,
                'currency' => 'AED',
                'vat_percentage' => 5.00,
                'show_trn' => true,
                'visible' => true,
                'minimum_items' => 5,
                'maximum_items' => 50,
                'minimum_options' => 0,
                'maximum_options' => 10,
                'need_professional_student_id' => false,
                'need_membership_id' => false,
                'needs_password' => false,
                'sponsored' => false,
                'send_to_dtcm' => true,
                'capacity' => 50,
                'color' => '#ef4444',
                'description' => 'Corporate team package with volume discounts. Includes priority seating and networking opportunities.',
                'sort_order' => 5,
                'is_active' => true,
                'category_types' => ['corporate', 'group'],
            ],
        ];

        foreach ($categories as $categoryData) {
            // Extract category types for later attachment
            $categoryTypeSlugs = $categoryData['category_types'] ?? [];
            unset($categoryData['category_types']);

            // Create the registration category
            $category = RegistrationCategory::create(array_merge($categoryData, [
                'event_id' => $eventId,
                'org_id' => $orgId,
            ]));

            // Attach category types
            if (!empty($categoryTypeSlugs)) {
                $categoryTypeIds = CategoryType::whereIn('slug', $categoryTypeSlugs)
                    ->pluck('id')
                    ->toArray();
                
                if (!empty($categoryTypeIds)) {
                    $category->categoryTypes()->attach($categoryTypeIds);
                }
            }

            $this->command->info("Created: {$category->name}");
        }

        $this->command->info('Registration Categories seeded successfully!');
    }
}
