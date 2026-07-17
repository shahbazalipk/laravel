<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Registration;
use App\Models\RegistrationCategory;
use App\Models\Exhibitor;
use App\Models\Group;
use App\Models\Event;
use App\Services\RegistrationService;

class RegistrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $registrationService = app(RegistrationService::class);
        $event = Event::getCurrentEvent();
        
        // Get first available category
        $category = RegistrationCategory::where('is_active', true)->first();
        
        if (!$category) {
            $this->command->error('No active registration categories found. Please create categories first.');
            return;
        }

        // Calculate pricing
        $pricing = $registrationService->calculatePrice($category, $event);

        // Create Individual Registrations
        $this->command->info('Creating individual registrations...');
        
        $individuals = [
            [
                'first_name' => 'John',
                'last_name' => 'Smith',
                'email' => 'john.smith@example.com',
                'phone' => '+971501234567',
                'company_name' => 'Tech Solutions LLC',
                'job_title' => 'Software Engineer',
            ],
            [
                'first_name' => 'Sarah',
                'last_name' => 'Johnson',
                'email' => 'sarah.johnson@example.com',
                'phone' => '+971502345678',
                'company_name' => 'Digital Marketing Agency',
                'job_title' => 'Marketing Manager',
            ],
            [
                'first_name' => 'Ahmed',
                'last_name' => 'Al-Mansoori',
                'email' => 'ahmed.almansoori@example.com',
                'phone' => '+971503456789',
                'company_name' => 'Emirates Consulting',
                'job_title' => 'Business Consultant',
            ],
        ];

        foreach ($individuals as $individual) {
            $registrationService->createRegistration(array_merge($individual, [
                'registration_category_id' => $category->id,
                'registration_type' => 'individual',
                'salutation' => 'Mr',
                'mobile_phone' => $individual['phone'],
                'department' => 'Operations',
                'company_size' => '50-100',
                'city' => 'Dubai',
                'country' => 'United Arab Emirates',
                'base_price' => $pricing['base_price'],
                'tax_amount' => $pricing['tax_amount'],
                'total_amount' => $pricing['total_amount'],
                'currency' => $pricing['currency'],
                'payment_status' => 'paid',
                'payment_method' => 'Credit Card',
                'terms_accepted' => true,
                'terms_accepted_at' => now(),
            ]));
        }

        $this->command->info('Created ' . count($individuals) . ' individual registrations.');

        // Create Exhibitor Registrations
        $exhibitor = Exhibitor::where('is_active', true)->first();
        
        if ($exhibitor) {
            $this->command->info('Creating exhibitor registrations...');
            
            $exhibitorReps = [
                [
                    'first_name' => 'Michael',
                    'last_name' => 'Chen',
                    'email' => 'michael.chen@' . strtolower(str_replace(' ', '', $exhibitor->company_name)) . '.com',
                    'phone' => '+971504567890',
                    'job_title' => 'Sales Director',
                ],
                [
                    'first_name' => 'Lisa',
                    'last_name' => 'Anderson',
                    'email' => 'lisa.anderson@' . strtolower(str_replace(' ', '', $exhibitor->company_name)) . '.com',
                    'phone' => '+971505678901',
                    'job_title' => 'Product Manager',
                ],
            ];

            foreach ($exhibitorReps as $rep) {
                $registrationService->createRegistration(array_merge($rep, [
                    'registration_category_id' => $category->id,
                    'registration_type' => 'exhibitor',
                    'exhibitor_id' => $exhibitor->id,
                    'salutation' => 'Ms',
                    'company_name' => $exhibitor->company_name,
                    'mobile_phone' => $rep['phone'],
                    'department' => 'Sales',
                    'company_size' => '100-500',
                    'city' => $exhibitor->city ?? 'Dubai',
                    'country' => $exhibitor->country ?? 'United Arab Emirates',
                    'company_address' => $exhibitor->address ?? 'Business Bay',
                    'base_price' => $pricing['base_price'],
                    'tax_amount' => $pricing['tax_amount'],
                    'total_amount' => $pricing['total_amount'],
                    'currency' => $pricing['currency'],
                    'payment_status' => 'paid',
                    'payment_method' => 'Bank Transfer',
                    'terms_accepted' => true,
                    'terms_accepted_at' => now(),
                ]));
            }

            $this->command->info('Created ' . count($exhibitorReps) . ' exhibitor registrations.');
        } else {
            $this->command->warn('No active exhibitors found. Skipping exhibitor registrations.');
        }

        // Create Group Registrations
        $group = Group::where('is_active', true)->first();
        
        if ($group) {
            $this->command->info('Creating group registrations...');
            
            $groupMembers = [
                [
                    'first_name' => 'David',
                    'last_name' => 'Williams',
                    'email' => 'david.williams@' . strtolower(str_replace(' ', '', $group->group_name)) . '.com',
                    'phone' => '+971506789012',
                    'job_title' => 'Team Leader',
                ],
                [
                    'first_name' => 'Emma',
                    'last_name' => 'Brown',
                    'email' => 'emma.brown@' . strtolower(str_replace(' ', '', $group->group_name)) . '.com',
                    'phone' => '+971507890123',
                    'job_title' => 'Project Coordinator',
                ],
                [
                    'first_name' => 'Mohammed',
                    'last_name' => 'Hassan',
                    'email' => 'mohammed.hassan@' . strtolower(str_replace(' ', '', $group->group_name)) . '.com',
                    'phone' => '+971508901234',
                    'job_title' => 'Technical Lead',
                ],
            ];

            foreach ($groupMembers as $member) {
                $registrationService->createRegistration(array_merge($member, [
                    'registration_category_id' => $category->id,
                    'registration_type' => 'group',
                    'group_id' => $group->id,
                    'salutation' => 'Mr',
                    'company_name' => $group->organization_name ?? $group->group_name,
                    'mobile_phone' => $member['phone'],
                    'department' => 'Operations',
                    'company_size' => '500+',
                    'city' => $group->city ?? 'Dubai',
                    'country' => $group->country ?? 'United Arab Emirates',
                    'company_address' => $group->address ?? 'Downtown Dubai',
                    'base_price' => $pricing['base_price'],
                    'tax_amount' => $pricing['tax_amount'],
                    'total_amount' => $pricing['total_amount'],
                    'currency' => $pricing['currency'],
                    'payment_status' => 'paid',
                    'payment_method' => 'Corporate Account',
                    'terms_accepted' => true,
                    'terms_accepted_at' => now(),
                ]));
            }

            $this->command->info('Created ' . count($groupMembers) . ' group registrations.');
        } else {
            $this->command->warn('No active groups found. Skipping group registrations.');
        }

        // Create some pending registrations
        $this->command->info('Creating pending registrations...');
        
        $pendingRegistrations = [
            [
                'first_name' => 'Robert',
                'last_name' => 'Taylor',
                'email' => 'robert.taylor@example.com',
                'phone' => '+971509012345',
                'company_name' => 'Startup Ventures',
                'job_title' => 'Founder',
                'payment_status' => 'pending',
            ],
            [
                'first_name' => 'Jennifer',
                'last_name' => 'Martinez',
                'email' => 'jennifer.martinez@example.com',
                'phone' => '+971500123456',
                'company_name' => 'Creative Studios',
                'job_title' => 'Creative Director',
                'payment_status' => 'pending',
            ],
        ];

        foreach ($pendingRegistrations as $pending) {
            $registrationService->createRegistration(array_merge($pending, [
                'registration_category_id' => $category->id,
                'registration_type' => 'individual',
                'salutation' => 'Ms',
                'mobile_phone' => $pending['phone'],
                'city' => 'Abu Dhabi',
                'country' => 'United Arab Emirates',
                'base_price' => $pricing['base_price'],
                'tax_amount' => $pricing['tax_amount'],
                'total_amount' => $pricing['total_amount'],
                'currency' => $pricing['currency'],
                'payment_method' => null,
                'terms_accepted' => true,
                'terms_accepted_at' => now(),
            ]));
        }

        $this->command->info('Created ' . count($pendingRegistrations) . ' pending registrations.');
        
        $this->command->info('✓ Registration seeding completed successfully!');
    }
}
