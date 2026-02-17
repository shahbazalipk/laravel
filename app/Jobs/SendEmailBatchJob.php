<?php

namespace App\Jobs;

use App\Models\EmailCampaign;
use App\Services\ProviderManager;
use App\Services\TemplateService;
use App\Services\TrackingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class SendEmailBatchJob implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $backoff = [60, 300, 900]; // 1min, 5min, 15min
    
    public EmailCampaign $campaign;
    public Collection $recipients;
    public int $batchNumber;
    
    /**
     * Create a new job instance.
     */
    public function __construct(EmailCampaign $campaign, Collection $recipients, int $batchNumber)
    {
        $this->campaign = $campaign;
        $this->recipients = $recipients;
        $this->batchNumber = $batchNumber;
        $this->onQueue('emails');
    }

    /**
     * Execute the job.
     */
    public function handle(
        ProviderManager $providerManager,
        TemplateService $templateService,
        TrackingService $trackingService
    ): void
    {
        Log::info("Processing email batch", [
            'campaign_id' => $this->campaign->id,
            'batch_number' => $this->batchNumber,
            'recipient_count' => $this->recipients->count(),
        ]);
        
        // Get email provider
        try {
            $provider = $providerManager->getProvider($this->campaign->provider_name);
        } catch (\Exception $e) {
            Log::error("Failed to get provider, using fallback", ['error' => $e->getMessage()]);
            $provider = $providerManager->handleFailover($e, null);
        }
        
        // Load template
        $template = $this->campaign->template;
        
        // Send emails
        foreach ($this->recipients as $recipient) {
            try {
                // Prepare merge data
                $mergeData = array_merge($recipient->merge_data ?? [], [
                    'event_name' => config('event.name', ''),
                    'event_date' => config('event.date', ''),
                    'event_location' => config('event.location', ''),
                    'unsubscribe_url' => route('email.unsubscribe', [
                        'hash' => encrypt($recipient->email . '|' . $this->campaign->id)
                    ]),
                ]);
                
                // Render template
                $rendered = $templateService->renderTemplate($template, $mergeData);
                
                // Send email
                $messageId = $provider->send(
                    $recipient->email,
                    $template->subject,
                    $rendered['html'],
                    $rendered['text'],
                    [
                        'from_email' => $this->campaign->sender_email,
                        'from_name' => $this->campaign->sender_name,
                        'reply_to' => $this->campaign->reply_to_email,
                    ]
                );
                
                // Update recipient status
                $recipient->update([
                    'status' => 'sent',
                    'message_id' => $messageId,
                    'sent_at' => now(),
                ]);
                
                // Log sent event
                $trackingService->logSent($this->campaign, $recipient->email, $messageId);
                
                // Increment campaign sent count
                $this->campaign->increment('sent_count');
                
            } catch (\Exception $e) {
                Log::error("Failed to send email", [
                    'campaign_id' => $this->campaign->id,
                    'recipient' => $recipient->email,
                    'error' => $e->getMessage(),
                ]);
                
                // Update recipient as failed
                $recipient->update([
                    'status' => 'failed',
                    'failed_at' => now(),
                    'error_message' => $e->getMessage(),
                ]);
                
                // Log failed event
                $trackingService->logFailed($recipient->message_id ?? 'unknown', $e->getMessage());
                
                // Increment campaign failed count
                $this->campaign->increment('failed_count');
            }
        }
        
        // Check if campaign is complete
        $this->checkCampaignCompletion();
        
        Log::info("Batch processing complete", [
            'campaign_id' => $this->campaign->id,
            'batch_number' => $this->batchNumber,
        ]);
    }
    
    /**
     * Get the middleware the job should pass through.
     */
    public function middleware(): array
    {
        return [new RateLimited('email-sending')];
    }
    
    /**
     * Check if all batches are complete
     */
    protected function checkCampaignCompletion(): void
    {
        $this->campaign->refresh();
        
        $totalProcessed = $this->campaign->sent_count + $this->campaign->failed_count;
        
        if ($totalProcessed >= $this->campaign->total_recipients) {
            $this->campaign->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
            
            Log::info("Campaign completed", ['campaign_id' => $this->campaign->id]);
        }
    }
    
    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Batch send failed", [
            'campaign_id' => $this->campaign->id,
            'batch_number' => $this->batchNumber,
            'error' => $exception->getMessage(),
        ]);
    }
}
