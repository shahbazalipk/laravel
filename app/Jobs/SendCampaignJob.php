<?php

namespace App\Jobs;

use App\Models\EmailCampaign;
use App\Services\RecipientService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendCampaignJob implements ShouldQueue
{
    use Queueable;

    public EmailCampaign $campaign;
    
    /**
     * Create a new job instance.
     */
    public function __construct(EmailCampaign $campaign)
    {
        $this->campaign = $campaign;
    }

    /**
     * Execute the job.
     */
    public function handle(RecipientService $recipientService): void
    {
        Log::info("Starting campaign send", ['campaign_id' => $this->campaign->id]);
        
        // Load recipients based on source
        $recipients = $this->loadRecipients($recipientService);
        
        // Apply deduplication and unsubscribe filtering
        $recipients = $recipientService->deduplicateEmails($recipients);
        $recipients = $recipientService->excludeUnsubscribed($recipients);
        
        // Update total recipient count
        $this->campaign->update([
            'total_recipients' => $recipients->count(),
        ]);
        
        // Store recipients in database
        foreach ($recipients as $recipient) {
            $this->campaign->recipients()->create([
                'event_id' => $this->campaign->event_id,
                'org_id' => $this->campaign->org_id,
                'email' => $recipient['email'],
                'first_name' => $recipient['first_name'] ?? '',
                'last_name' => $recipient['last_name'] ?? '',
                'merge_data' => $recipient,
                'status' => 'pending',
            ]);
        }
        
        // Chunk recipients into batches of 100
        $batches = $this->campaign->recipients()->where('status', 'pending')->get()->chunk(100);
        
        // Dispatch batch jobs
        foreach ($batches as $batchNumber => $batch) {
            SendEmailBatchJob::dispatch($this->campaign, $batch, $batchNumber + 1);
        }
        
        Log::info("Campaign batches dispatched", [
            'campaign_id' => $this->campaign->id,
            'batch_count' => $batches->count(),
        ]);
    }
    
    /**
     * Load recipients based on campaign source
     */
    protected function loadRecipients(RecipientService $recipientService)
    {
        switch ($this->campaign->recipient_source) {
            case 'registrations':
                return $recipientService->resolveFromRegistrations(
                    $this->campaign->recipient_filters ?? []
                );
            
            case 'csv':
                // CSV recipients would already be stored
                return $this->campaign->recipients->map(function ($r) {
                    return $r->merge_data;
                });
            
            case 'segment':
                return $recipientService->resolveFromSegment(
                    $this->campaign->recipient_filters['segment_id'] ?? null
                );
            
            default:
                return collect();
        }
    }
    
    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Campaign send failed", [
            'campaign_id' => $this->campaign->id,
            'error' => $exception->getMessage(),
        ]);
        
        $this->campaign->update([
            'status' => 'failed',
        ]);
    }
}
