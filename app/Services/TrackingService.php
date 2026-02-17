<?php

namespace App\Services;

use App\Models\EmailCampaign;
use App\Models\EmailCampaignLog;
use App\Models\EmailCampaignRecipient;

class TrackingService
{
    /**
     * Log email sent event
     * 
     * @param EmailCampaign $campaign
     * @param string $email
     * @param string $messageId
     * @return void
     */
    public function logSent(EmailCampaign $campaign, string $email, string $messageId): void
    {
        $recipient = $campaign->recipients()->where('email', $email)->first();
        
        EmailCampaignLog::create([
            'event_id' => $campaign->event_id,
            'org_id' => $campaign->org_id,
            'email_campaign_id' => $campaign->id,
            'email_campaign_recipient_id' => $recipient?->id,
            'event_type' => 'sent',
            'message_id' => $messageId,
            'email' => $email,
            'occurred_at' => now(),
        ]);
    }
    
    /**
     * Log email delivered event
     * 
     * @param string $messageId
     * @return void
     */
    public function logDelivered(string $messageId): void
    {
        $recipient = EmailCampaignRecipient::where('message_id', $messageId)->first();
        
        if ($recipient) {
            $recipient->update([
                'status' => 'delivered',
                'delivered_at' => now(),
            ]);
            
            EmailCampaignLog::create([
                'event_id' => $recipient->event_id,
                'org_id' => $recipient->org_id,
                'email_campaign_id' => $recipient->email_campaign_id,
                'email_campaign_recipient_id' => $recipient->id,
                'event_type' => 'delivered',
                'message_id' => $messageId,
                'email' => $recipient->email,
                'occurred_at' => now(),
            ]);
            
            $recipient->campaign->increment('delivered_count');
        }
    }
    
    /**
     * Log email opened event
     * 
     * @param string $messageId
     * @return void
     */
    public function logOpened(string $messageId): void
    {
        $recipient = EmailCampaignRecipient::where('message_id', $messageId)->first();
        
        if ($recipient && !$recipient->opened_at) {
            $recipient->update([
                'status' => 'opened',
                'opened_at' => now(),
            ]);
            
            EmailCampaignLog::create([
                'event_id' => $recipient->event_id,
                'org_id' => $recipient->org_id,
                'email_campaign_id' => $recipient->email_campaign_id,
                'email_campaign_recipient_id' => $recipient->id,
                'event_type' => 'opened',
                'message_id' => $messageId,
                'email' => $recipient->email,
                'occurred_at' => now(),
            ]);
            
            $recipient->campaign->increment('opened_count');
        }
    }
    
    /**
     * Log email clicked event
     * 
     * @param string $messageId
     * @param string $url
     * @return void
     */
    public function logClicked(string $messageId, string $url): void
    {
        $recipient = EmailCampaignRecipient::where('message_id', $messageId)->first();
        
        if ($recipient && !$recipient->clicked_at) {
            $recipient->update([
                'status' => 'clicked',
                'clicked_at' => now(),
            ]);
            
            $recipient->campaign->increment('clicked_count');
        }
        
        if ($recipient) {
            EmailCampaignLog::create([
                'event_id' => $recipient->event_id,
                'org_id' => $recipient->org_id,
                'email_campaign_id' => $recipient->email_campaign_id,
                'email_campaign_recipient_id' => $recipient->id,
                'event_type' => 'clicked',
                'message_id' => $messageId,
                'email' => $recipient->email,
                'url' => $url,
                'occurred_at' => now(),
            ]);
        }
    }
    
    /**
     * Log email bounced event
     * 
     * @param string $messageId
     * @param string $reason
     * @return void
     */
    public function logBounced(string $messageId, string $reason): void
    {
        $recipient = EmailCampaignRecipient::where('message_id', $messageId)->first();
        
        if ($recipient) {
            $recipient->update([
                'status' => 'bounced',
                'bounced_at' => now(),
                'error_message' => $reason,
            ]);
            
            EmailCampaignLog::create([
                'event_id' => $recipient->event_id,
                'org_id' => $recipient->org_id,
                'email_campaign_id' => $recipient->email_campaign_id,
                'email_campaign_recipient_id' => $recipient->id,
                'event_type' => 'bounced',
                'message_id' => $messageId,
                'email' => $recipient->email,
                'metadata' => ['reason' => $reason],
                'occurred_at' => now(),
            ]);
            
            $recipient->campaign->increment('bounced_count');
        }
    }
    
    /**
     * Log email failed event
     * 
     * @param string $messageId
     * @param string $error
     * @return void
     */
    public function logFailed(string $messageId, string $error): void
    {
        $recipient = EmailCampaignRecipient::where('message_id', $messageId)->first();
        
        if ($recipient) {
            EmailCampaignLog::create([
                'event_id' => $recipient->event_id,
                'org_id' => $recipient->org_id,
                'email_campaign_id' => $recipient->email_campaign_id,
                'email_campaign_recipient_id' => $recipient->id,
                'event_type' => 'failed',
                'message_id' => $messageId,
                'email' => $recipient->email,
                'metadata' => ['error' => $error],
                'occurred_at' => now(),
            ]);
        }
    }
    
    /**
     * Get recipient status
     * 
     * @param EmailCampaign $campaign
     * @param string $email
     * @return array
     */
    public function getRecipientStatus(EmailCampaign $campaign, string $email): array
    {
        $recipient = $campaign->recipients()->where('email', $email)->first();
        
        if (!$recipient) {
            return [];
        }
        
        return [
            'email' => $recipient->email,
            'status' => $recipient->status,
            'sent_at' => $recipient->sent_at?->toIso8601String(),
            'delivered_at' => $recipient->delivered_at?->toIso8601String(),
            'opened_at' => $recipient->opened_at?->toIso8601String(),
            'clicked_at' => $recipient->clicked_at?->toIso8601String(),
            'bounced_at' => $recipient->bounced_at?->toIso8601String(),
            'failed_at' => $recipient->failed_at?->toIso8601String(),
            'error_message' => $recipient->error_message,
        ];
    }
    
    /**
     * Get campaign statistics
     * 
     * @param EmailCampaign $campaign
     * @return array
     */
    public function getCampaignStatistics(EmailCampaign $campaign): array
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
            'bounce_rate' => $campaign->sent_count > 0 
                ? ($campaign->bounced_count / $campaign->sent_count) * 100 
                : 0,
        ];
    }
    
    /**
     * Aggregate statistics for campaign
     * 
     * @param EmailCampaign $campaign
     * @return void
     */
    public function aggregateStatistics(EmailCampaign $campaign): void
    {
        $campaign->update([
            'sent_count' => $campaign->recipients()->where('status', '!=', 'pending')->count(),
            'delivered_count' => $campaign->recipients()->whereNotNull('delivered_at')->count(),
            'opened_count' => $campaign->recipients()->whereNotNull('opened_at')->count(),
            'clicked_count' => $campaign->recipients()->whereNotNull('clicked_at')->count(),
            'bounced_count' => $campaign->recipients()->whereNotNull('bounced_at')->count(),
            'failed_count' => $campaign->recipients()->whereNotNull('failed_at')->count(),
        ]);
    }
}
