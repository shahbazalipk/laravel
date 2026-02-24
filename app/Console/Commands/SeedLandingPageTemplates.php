<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\LandingPageTemplateSeeder;

class SeedLandingPageTemplates extends Command
{
    protected $signature = 'templates:seed {--force : Force seeding even if templates exist}';
    protected $description = 'Seed landing page templates for the current event';

    public function handle()
    {
        $force = $this->option('force');
        
        if ($force) {
            $this->warn('Force mode: Deleting existing templates...');
            \App\Models\LandingPageTemplate::where('event_id', config('event.event_id'))
                ->where('org_id', config('event.org_id'))
                ->delete();
        }
        
        $this->info('Seeding landing page templates...');
        
        $seeder = new LandingPageTemplateSeeder();
        $seeder->setCommand($this);
        $seeder->run();
        
        $count = \App\Models\LandingPageTemplate::where('event_id', config('event.event_id'))
            ->where('org_id', config('event.org_id'))
            ->count();
            
        $this->info("Total templates for this event: {$count}");
        
        return 0;
    }
}
