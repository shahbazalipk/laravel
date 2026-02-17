<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\AgendaItem;

class EventDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create categories (tracks)
        $categories = [
            [
                'name' => 'Technology',
                'description' => 'Tech talks and innovations',
                'color' => '#3B82F6',
                'sort_order' => 1,
            ],
            [
                'name' => 'Business',
                'description' => 'Business strategies and insights',
                'color' => '#10B981',
                'sort_order' => 2,
            ],
            [
                'name' => 'Design',
                'description' => 'Creative design sessions',
                'color' => '#F59E0B',
                'sort_order' => 3,
            ],
        ];

        foreach ($categories as $categoryData) {
            $category = Category::create($categoryData);
            
            // Create agenda items for each category
            AgendaItem::create([
                'track_id' => $category->id,
                'title' => 'Opening Keynote - ' . $category->name,
                'description' => 'Join us for an inspiring opening keynote about ' . strtolower($category->name),
                'session_type' => 'keynote',
                'start_time' => now()->addDays(7)->setTime(9, 0),
                'end_time' => now()->addDays(7)->setTime(10, 0),
                'duration' => 60,
                'is_featured' => true,
            ]);
            
            AgendaItem::create([
                'track_id' => $category->id,
                'title' => 'Workshop: Advanced ' . $category->name,
                'description' => 'Hands-on workshop covering advanced topics in ' . strtolower($category->name),
                'session_type' => 'workshop',
                'start_time' => now()->addDays(7)->setTime(11, 0),
                'end_time' => now()->addDays(7)->setTime(13, 0),
                'duration' => 120,
                'capacity' => 50,
                'level' => 'Advanced',
            ]);
        }
        
        // Add a networking break
        AgendaItem::create([
            'title' => 'Networking Break',
            'description' => 'Coffee and networking opportunity',
            'session_type' => 'networking',
            'is_break' => true,
            'start_time' => now()->addDays(7)->setTime(10, 30),
            'end_time' => now()->addDays(7)->setTime(11, 0),
            'duration' => 30,
        ]);
    }
}
