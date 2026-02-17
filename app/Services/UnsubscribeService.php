<?php

namespace App\Services;

use App\Models\EmailUnsubscribe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class UnsubscribeService
{
    /**
     * Unsubscribe an email address
     * 
     * @param string $email
     * @param string|null $reason
     * @return void
     */
    public function unsubscribe(string $email, ?string $reason = null): void
    {
        EmailUnsubscribe::updateOrCreate(
            [
                'event_id' => config('event.event_id'),
                'org_id' => config('event.org_id'),
                'email' => $email,
            ],
            [
                'reason' => $reason,
                'unsubscribed_at' => now(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]
        );
    }
    
    /**
     * Resubscribe an email address
     * 
     * @param string $email
     * @return void
     */
    public function resubscribe(string $email): void
    {
        EmailUnsubscribe::where('email', $email)->delete();
    }
    
    /**
     * Check if email is unsubscribed
     * 
     * @param string $email
     * @return bool
     */
    public function isUnsubscribed(string $email): bool
    {
        return EmailUnsubscribe::where('email', $email)->exists();
    }
    
    /**
     * Get unsubscribe URL for email and campaign
     * 
     * @param string $email
     * @param int $campaignId
     * @return string
     */
    public function getUnsubscribeUrl(string $email, int $campaignId): string
    {
        $hash = Crypt::encryptString($email . '|' . $campaignId);
        
        return route('email.unsubscribe', ['hash' => $hash]);
    }
    
    /**
     * Handle unsubscribe request from hash
     * 
     * @param Request $request
     * @return array ['email' => string, 'campaign_id' => int]
     * @throws \Exception
     */
    public function handleUnsubscribeRequest(Request $request): array
    {
        $hash = $request->input('hash');
        
        if (!$hash) {
            throw new \Exception('Invalid unsubscribe link');
        }
        
        try {
            $decrypted = Crypt::decryptString($hash);
            [$email, $campaignId] = explode('|', $decrypted);
            
            return [
                'email' => $email,
                'campaign_id' => (int) $campaignId,
            ];
        } catch (\Exception $e) {
            throw new \Exception('Invalid or expired unsubscribe link');
        }
    }
    
    /**
     * Export unsubscribe list
     * 
     * @return \Illuminate\Support\Collection
     */
    public function exportUnsubscribeList()
    {
        return EmailUnsubscribe::all();
    }
}
