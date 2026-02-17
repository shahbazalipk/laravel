<?php

namespace App\Services;

use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CampaignService
{
    protected RecipientService $recipientService;
    
    public function __construct(RecipientService $recipientService)
    {
        $this->recipientService = $recipientService;
    }
    
    /**
     * Create a new campaign
     * 
     * @param array $data
     * @return EmailCampaign
     */
    public function create(array $data): EmailCampaign
    {
        $data['status'] = 'draft';
        
        return EmailCampaign::create($data);
    }
    
    /**
     * Update a campaign
     * 
     * @param EmailCampaign $campaign
     * @param array $data
     * @return EmailCampaign
     */
    public function update(EmailCampaign $campaign, array $data): EmailCampaign
    {
        $campaign->update($data);
        
        return $campaign->fresh();
    }
    
    /**
     * Delete a campaign
     * 
     * @param EmailCampaign $campaign
     * @return bool
     */
    public function delete(EmailCampaign $campaign): bool
    {
        return $campaign->delete();
    }
    
    /**
     * Schedule a campaign
     * 
     * @param EmailCampaign $campaign
     * @param Carbon|null $sendAt
     * @return void
     */
    public function schedule(EmailCampaign $campaign, ?Carbon $sendAt = null): void
    {
        $campaign->update([
            'status' => 'scheduled',
            'scheduled_at' => $sendAt ?? now(),
        ]);
    }
    
    /**
     * Send a campaign
     * 
     * @param EmailCampaign $campaign
     * @return void
     */
    public function send(EmailCampaign $campaign): void
    {
        // Update status to sending
        $campaign->update([
            'status' => 'sending',
            'started_at' => now(),
        ]);
        
        // Dispatch queue job to handle sending
        \App\Jobs\SendCampaignJob::dispatch($campaign);
    }
    
    /**
     * Pause a campaign
     * 
     * @param EmailCampaign $campaign
     * @return void
     */
    public function pause(EmailCampaign $campaign): void
    {
        $campaign->update([
            'status' => 'paused',
            'paused_at' => now(),
        ]);
    }
    
    /**
     * Resume a paused campaign
     * 
     * @param EmailCampaign $campaign
     * @return void
     */
    public function resume(EmailCampaign $campaign): void
    {
        $campaign->update([
            'status' => 'sending',
            'paused_at' => null,
        ]);
        
        // Re-dispatch queue job
        \App\Jobs\SendCampaignJob::dispatch($campaign);
    }
    
    /**
     * Cancel a campaign
     * 
     * @param EmailCampaign $campaign
     * @return void
     */
    public function cancel(EmailCampaign $campaign): void
    {
        $campaign->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }
    
    /**
     * Get campaign statistics
     * 
     * @param EmailCampaign $campaign
     * @return array
     */
    public function getStatistics(EmailCampaign $campaign): array
    {
        return [
            'total_recipients' => $campaign->total_recipients,
            'sent' => $campaign->sent_count,
            'delivered' => $campaign->delivered_count,
            'opened' => $campaign->opened_count,
            'clicked' => $campaign->clicked_count,
            'bounced' => $campaign->bounced_count,
            'failed' => $campaign->failed_count,
            'unsubscribed' => $campaign->unsubscribed_count,
            'delivery_rate' => $campaign->delivery_rate,
            'open_rate' => $campaign->open_rate,
            'click_rate' => $campaign->click_rate,
        ];
    }
    
    /**
     * Export campaign report as CSV
     * 
     * @param EmailCampaign $campaign
     * @return string Path to CSV file
     */
    public function exportReport(EmailCampaign $campaign): string
    {
        $filename = storage_path('app/exports/campaign-' . $campaign->id . '-' . time() . '.csv');
        
        $handle = fopen($filename, 'w');
        
        // Write header
        fputcsv($handle, [
            'Email',
            'First Name',
            'Last Name',
            'Status',
            'Sent At',
            'Delivered At',
            'Opened At',
            'Clicked At',
            'Bounced At',
            'Failed At',
            'Error Message',
        ]);
        
        // Write recipient data
        $campaign->recipients()->chunk(1000, function ($recipients) use ($handle) {
            foreach ($recipients as $recipient) {
                fputcsv($handle, [
                    $recipient->email,
                    $recipient->first_name,
                    $recipient->last_name,
                    $recipient->status,
                    $recipient->sent_at?->toDateTimeString(),
                    $recipient->delivered_at?->toDateTimeString(),
                    $recipient->opened_at?->toDateTimeString(),
                    $recipient->clicked_at?->toDateTimeString(),
                    $recipient->bounced_at?->toDateTimeString(),
                    $recipient->failed_at?->toDateTimeString(),
                    $recipient->error_message,
                ]);
            }
        });
        
        fclose($handle);
        
        return $filename;
    }
}
