<?php

namespace App\Console\Commands;

use App\Models\EmailCampaign;
use App\Services\CampaignService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessScheduledCampaigns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'campaigns:process-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process scheduled email campaigns that are ready to be sent';

    /**
     * Execute the console command.
     */
    public function handle(CampaignService $campaignService)
    {
        $this->info('Processing scheduled campaigns...');
        
        // Query campaigns with status=scheduled and scheduled_at <= now
        $campaigns = EmailCampaign::scheduled()->get();
        
        if ($campaigns->isEmpty()) {
            $this->info('No scheduled campaigns found.');
            Log::info('ProcessScheduledCampaigns: No campaigns to process');
            return Command::SUCCESS;
        }
        
        $this->line("Found {$campaigns->count()} campaign(s) ready to send.");
        
        $processed = 0;
        $failed = 0;
        
        foreach ($campaigns as $campaign) {
            try {
                $this->line("Processing campaign: {$campaign->name} (ID: {$campaign->id})");
                
                // Call CampaignService.send() for each campaign
                $campaignService->send($campaign);
                
                $processed++;
                $this->info("✓ Campaign '{$campaign->name}' queued for sending");
                
                // Log successful processing
                Log::info('ProcessScheduledCampaigns: Campaign queued', [
                    'campaign_id' => $campaign->id,
                    'campaign_name' => $campaign->name,
                    'event_id' => $campaign->event_id,
                    'org_id' => $campaign->org_id,
                ]);
                
            } catch (\Exception $e) {
                $failed++;
                $this->error("✗ Failed to process campaign '{$campaign->name}': {$e->getMessage()}");
                
                // Log failure
                Log::error('ProcessScheduledCampaigns: Failed to process campaign', [
                    'campaign_id' => $campaign->id,
                    'campaign_name' => $campaign->name,
                    'event_id' => $campaign->event_id,
                    'org_id' => $campaign->org_id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }
        
        $this->newLine();
        $this->info("Processing complete:");
        $this->line("  - Processed: <fg=green>{$processed}</>");
        $this->line("  - Failed: <fg=red>{$failed}</>");
        
        // Log summary
        Log::info('ProcessScheduledCampaigns: Processing complete', [
            'total_campaigns' => $campaigns->count(),
            'processed' => $processed,
            'failed' => $failed,
        ]);
        
        return Command::SUCCESS;
    }
}
