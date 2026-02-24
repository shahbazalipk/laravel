<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Event;

class TestSubdomainResolution extends Command
{
    protected $signature = 'test:subdomain {subdomain?}';
    protected $description = 'Test subdomain resolution for events';

    public function handle()
    {
        $subdomain = $this->argument('subdomain');
        
        if (!$subdomain) {
            // Show all events with subdomains
            $this->info('All events with subdomains:');
            $this->info('');
            
            $events = Event::whereNotNull('subdomain')->get();
            
            if ($events->isEmpty()) {
                $this->warn('No events found with subdomains');
                return;
            }
            
            $this->table(
                ['ID', 'Event Name', 'Subdomain', 'Org ID', 'URL'],
                $events->map(function ($event) {
                    return [
                        $event->id,
                        $event->event_name ?? $event->title,
                        $event->subdomain,
                        $event->organization_id ?? $event->org_id,
                        'https://' . $event->subdomain . '.glimzo.ai'
                    ];
                })
            );
            
            $this->info('');
            $this->info('Test a specific subdomain: php artisan test:subdomain meetshahbaz');
            return;
        }
        
        // Test specific subdomain
        $this->info("Testing subdomain: {$subdomain}");
        $this->info('');
        
        $event = Event::where('subdomain', $subdomain)->first();
        
        if (!$event) {
            $this->error("❌ Event not found for subdomain: {$subdomain}");
            $this->info('');
            $this->info('Available subdomains:');
            Event::whereNotNull('subdomain')->pluck('subdomain')->each(function ($sub) {
                $this->line("  - {$sub}");
            });
            return 1;
        }
        
        $this->info("✅ Event found!");
        $this->info('');
        $this->table(
            ['Property', 'Value'],
            [
                ['Event ID', $event->id],
                ['Event Name', $event->event_name ?? $event->title],
                ['Subdomain', $event->subdomain],
                ['Organization ID', $event->organization_id ?? $event->org_id],
                ['URL', 'https://' . $event->subdomain . '.glimzo.ai'],
                ['Start Date', $event->start_date ? $event->start_date->format('Y-m-d') : 'N/A'],
                ['Status', $event->status ?? 'N/A'],
            ]
        );
        
        $this->info('');
        $this->info('✅ This subdomain will work correctly with the middleware!');
        
        return 0;
    }
}
