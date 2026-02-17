<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Agenda;
use App\Models\Track;
use App\Models\Location;
use App\Models\Speaker;
use App\Models\Session;
use App\Models\Lecture;
use Carbon\Carbon;

class AgendaSeeder extends Seeder
{
    public function run(): void
    {
        // Create an Agenda
        $agenda = Agenda::create([
            'title' => '2026 Annual Conference',
            'description' => 'Our flagship annual conference featuring industry leaders and innovative sessions',
            'start_date' => Carbon::now()->addMonths(2)->startOfDay(),
            'end_date' => Carbon::now()->addMonths(2)->addDays(2)->endOfDay(),
            'status' => 'published',
        ]);

        // Create Tracks
        $tracks = [
            Track::create([
                'name' => 'Technology & Innovation',
                'description' => 'Latest trends in technology and digital transformation',
                'color' => '#3B82F6',
                'sort_order' => 1,
                'agenda_id' => $agenda->id,
            ]),
            Track::create([
                'name' => 'Business Strategy',
                'description' => 'Strategic planning and business development',
                'color' => '#10B981',
                'sort_order' => 2,
                'agenda_id' => $agenda->id,
            ]),
            Track::create([
                'name' => 'Leadership & Management',
                'description' => 'Leadership skills and team management',
                'color' => '#F59E0B',
                'sort_order' => 3,
                'agenda_id' => $agenda->id,
            ]),
        ];

        // Create Locations
        $locations = [
            Location::create([
                'name' => 'Main Auditorium',
                'address' => 'Convention Center, 1st Floor',
                'room_number' => 'A-101',
                'capacity' => 500,
            ]),
            Location::create([
                'name' => 'Conference Room A',
                'address' => 'Convention Center, 2nd Floor',
                'room_number' => 'B-201',
                'capacity' => 100,
            ]),
            Location::create([
                'name' => 'Conference Room B',
                'address' => 'Convention Center, 2nd Floor',
                'room_number' => 'B-202',
                'capacity' => 100,
            ]),
            Location::create([
                'name' => 'Workshop Hall',
                'address' => 'Convention Center, 3rd Floor',
                'room_number' => 'C-301',
                'capacity' => 50,
            ]),
        ];

        // Create Speakers
        $speakers = [
            Speaker::create([
                'full_name' => 'Dr. Sarah Johnson',
                'bio' => 'Leading expert in AI and machine learning with 15 years of experience',
                'email' => 'sarah.johnson@example.com',
                'phone' => '+1-555-0101',
                'company' => 'Tech Innovations Inc.',
                'job_title' => 'Chief Technology Officer',
            ]),
            Speaker::create([
                'full_name' => 'Michael Chen',
                'bio' => 'Serial entrepreneur and business strategist',
                'email' => 'michael.chen@example.com',
                'phone' => '+1-555-0102',
                'company' => 'Growth Partners LLC',
                'job_title' => 'CEO & Founder',
            ]),
            Speaker::create([
                'full_name' => 'Emily Rodriguez',
                'bio' => 'Award-winning leadership coach and author',
                'email' => 'emily.rodriguez@example.com',
                'phone' => '+1-555-0103',
                'company' => 'Leadership Excellence',
                'job_title' => 'Executive Coach',
            ]),
            Speaker::create([
                'full_name' => 'David Kim',
                'bio' => 'Digital transformation consultant with Fortune 500 experience',
                'email' => 'david.kim@example.com',
                'phone' => '+1-555-0104',
                'company' => 'Digital Dynamics',
                'job_title' => 'Senior Consultant',
            ]),
        ];

        // Day 1 Sessions
        $day1Start = $agenda->start_date->copy()->setTime(9, 0);
        
        // Keynote Session
        $keynote = Session::create([
            'title' => 'Opening Keynote: The Future of Innovation',
            'description' => 'Join us for an inspiring opening keynote about the future of technology and innovation',
            'type' => 'keynote',
            'start_time' => $day1Start,
            'end_time' => $day1Start->copy()->addHour(),
            'agenda_id' => $agenda->id,
            'track_id' => $tracks[0]->id,
            'location_id' => $locations[0]->id,
            'requires_speakers' => true,
            'max_attendees' => 500,
        ]);
        $keynote->speakers()->attach($speakers[0]->id, ['role' => 'Keynote Speaker']);

        // Coffee Break
        Session::create([
            'title' => 'Morning Coffee Break',
            'description' => 'Networking and refreshments',
            'type' => 'break',
            'start_time' => $day1Start->copy()->addHours(1),
            'end_time' => $day1Start->copy()->addHours(1)->addMinutes(30),
            'agenda_id' => $agenda->id,
            'track_id' => null,
            'location_id' => $locations[0]->id,
            'requires_speakers' => false,
        ]);

        // Panel Discussion
        $panel = Session::create([
            'title' => 'Panel: Digital Transformation Strategies',
            'description' => 'Industry leaders discuss successful digital transformation initiatives',
            'type' => 'panel',
            'start_time' => $day1Start->copy()->addHours(1)->addMinutes(30),
            'end_time' => $day1Start->copy()->addHours(2)->addMinutes(30),
            'agenda_id' => $agenda->id,
            'track_id' => $tracks[0]->id,
            'location_id' => $locations[0]->id,
            'requires_speakers' => true,
            'max_attendees' => 500,
        ]);
        $panel->speakers()->attach($speakers[1]->id, ['role' => 'Moderator']);
        $panel->speakers()->attach($speakers[3]->id, ['role' => 'Panelist']);

        // Workshop
        $workshop = Session::create([
            'title' => 'Workshop: Leadership in the Digital Age',
            'description' => 'Interactive workshop on modern leadership techniques',
            'type' => 'workshop',
            'start_time' => $day1Start->copy()->addHours(2)->addMinutes(30),
            'end_time' => $day1Start->copy()->addHours(4)->addMinutes(30),
            'agenda_id' => $agenda->id,
            'track_id' => $tracks[2]->id,
            'location_id' => $locations[3]->id,
            'requires_speakers' => true,
            'max_attendees' => 50,
        ]);
        $workshop->speakers()->attach($speakers[2]->id, ['role' => 'Workshop Leader']);

        // Lunch Break
        Session::create([
            'title' => 'Lunch Break',
            'description' => 'Catered lunch and networking',
            'type' => 'break',
            'start_time' => $day1Start->copy()->addHours(4)->addMinutes(30),
            'end_time' => $day1Start->copy()->addHours(5)->addMinutes(30),
            'agenda_id' => $agenda->id,
            'track_id' => null,
            'location_id' => $locations[0]->id,
            'requires_speakers' => false,
        ]);

        // Afternoon Talk with Lectures
        $talk = Session::create([
            'title' => 'AI and Machine Learning Applications',
            'description' => 'Deep dive into practical AI applications',
            'type' => 'talk',
            'start_time' => $day1Start->copy()->addHours(5)->addMinutes(30),
            'end_time' => $day1Start->copy()->addHours(7),
            'agenda_id' => $agenda->id,
            'track_id' => $tracks[0]->id,
            'location_id' => $locations[1]->id,
            'requires_speakers' => true,
            'max_attendees' => 100,
        ]);
        $talk->speakers()->attach($speakers[0]->id);

        // Create Lectures for the talk
        Lecture::create([
            'topic' => 'Introduction to Machine Learning',
            'description' => 'Fundamentals of ML and its applications',
            'session_id' => $talk->id,
            'speaker_id' => $speakers[0]->id,
            'location_id' => $locations[1]->id,
            'start_time' => $talk->start_time,
            'end_time' => $talk->start_time->copy()->addMinutes(45),
        ]);

        Lecture::create([
            'topic' => 'Real-world AI Case Studies',
            'description' => 'Success stories from industry implementations',
            'session_id' => $talk->id,
            'speaker_id' => $speakers[0]->id,
            'location_id' => $locations[1]->id,
            'start_time' => $talk->start_time->copy()->addMinutes(45),
            'end_time' => $talk->end_time,
        ]);

        // Networking Session
        Session::create([
            'title' => 'Evening Networking Reception',
            'description' => 'Casual networking with drinks and appetizers',
            'type' => 'networking',
            'start_time' => $day1Start->copy()->addHours(7),
            'end_time' => $day1Start->copy()->addHours(8)->addMinutes(30),
            'agenda_id' => $agenda->id,
            'track_id' => null,
            'location_id' => $locations[0]->id,
            'requires_speakers' => false,
            'max_attendees' => 500,
        ]);

        $this->command->info('Agenda system seeded successfully!');
        $this->command->info('Created: 1 Agenda, 3 Tracks, 4 Locations, 4 Speakers, 8 Sessions, 2 Lectures');
    }
}
