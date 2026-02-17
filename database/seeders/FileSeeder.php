<?php

namespace Database\Seeders;

use App\Models\File;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class FileSeeder extends Seeder
{
    public function run(): void
    {
        $eventId = config('event.event_id');
        $orgId = config('event.org_id');

        // Create sample files directory if it doesn't exist
        if (!Storage::disk('public')->exists('files')) {
            Storage::disk('public')->makeDirectory('files');
        }

        // Create sample text files for demonstration
        $sampleFiles = [
            [
                'name' => 'Event Guidelines',
                'original_name' => 'event-guidelines.pdf',
                'file_path' => 'files/sample-guidelines.txt',
                'mime_type' => 'application/pdf',
                'file_size' => 2048576, // 2MB
                'category' => 'document',
                'description' => 'Official event guidelines and rules for attendees',
                'is_public' => true,
            ],
            [
                'name' => 'Venue Map',
                'original_name' => 'venue-map.png',
                'file_path' => 'files/sample-map.txt',
                'mime_type' => 'image/png',
                'file_size' => 1536000, // 1.5MB
                'category' => 'image',
                'description' => 'Interactive map of the event venue',
                'is_public' => true,
            ],
            [
                'name' => 'Keynote Recording',
                'original_name' => 'keynote-2024.mp4',
                'file_path' => 'files/sample-video.txt',
                'mime_type' => 'video/mp4',
                'file_size' => 52428800, // 50MB
                'category' => 'video',
                'description' => 'Recording of the opening keynote session',
                'is_public' => false,
            ],
            [
                'name' => 'Sponsor Logos Package',
                'original_name' => 'sponsor-logos.zip',
                'file_path' => 'files/sample-logos.txt',
                'mime_type' => 'application/zip',
                'file_size' => 5242880, // 5MB
                'category' => 'other',
                'description' => 'High-resolution logos of all event sponsors',
                'is_public' => false,
            ],
            [
                'name' => 'Background Music',
                'original_name' => 'event-theme.mp3',
                'file_path' => 'files/sample-audio.txt',
                'mime_type' => 'audio/mpeg',
                'file_size' => 3145728, // 3MB
                'category' => 'audio',
                'description' => 'Official event theme music',
                'is_public' => true,
            ],
        ];

        foreach ($sampleFiles as $fileData) {
            // Create a sample file
            Storage::disk('public')->put($fileData['file_path'], 'Sample file content for ' . $fileData['name']);

            File::create(array_merge($fileData, [
                'event_id' => $eventId,
                'org_id' => $orgId,
                'uploaded_by' => 1, // Assuming admin ID 1
            ]));
        }
    }
}
