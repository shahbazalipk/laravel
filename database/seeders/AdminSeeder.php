<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Check if admin already exists
        $existingAdmin = Admin::where('email', 'admin@event.com')->first();
        
        if (!$existingAdmin) {
            Admin::create([
                'name' => 'Event Administrator',
                'email' => 'admin@event.com',
                'password' => 'password', // Will be hashed by the model
            ]);
            
            echo "Admin user created: admin@event.com / password\n";
        } else {
            echo "Admin user already exists\n";
        }
    }
}
