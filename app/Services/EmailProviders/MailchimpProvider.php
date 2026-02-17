<?php

namespace App\Services\EmailProviders;

use App\Contracts\EmailProviderInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MailchimpProvider implements EmailProviderInterface
{
    protected array $credentials;
    protected string $baseUrl = 'https://mandrillapp.com/api/1.0';
    
    public function __construct(array $credentials)
    {
        $this->credentials = $credentials;
    }
    
    public function send(string $to, string $subject, string $html, string $text, array $metadata): string
    {
        $response = Http::post($this->baseUrl . '/messages/send', [
            'key' => $this->credentials['api_key'],
            'message' => [
                'html' => $html,
                'text' => $text,
                'subject' => $subject,
                'from_email' => $metadata['from_email'] ?? config('mail.from.address'),
                'from_name' => $metadata['from_name'] ?? config('mail.from.name'),
                'to' => [
                    [
                        'email' => $to,
                        'type' => 'to',
                    ],
                ],
                'track_opens' => true,
                'track_clicks' => true,
            ],
        ]);
        
        if ($response->successful()) {
            $result = $response->json();
            return $result[0]['_id'] ?? 'unknown';
        }
        
        throw new \Exception('Mailchimp send failed: ' . $response->body());
    }
    
    public function sendBatch(array $recipients, string $subject, string $html, string $text): array
    {
        $results = [];
        
        foreach ($recipients as $recipient) {
            try {
                $messageId = $this->send(
                    $recipient['email'],
                    $subject,
                    $html,
                    $text,
                    $recipient['metadata'] ?? []
                );
                
                $results[$recipient['email']] = $messageId;
            } catch (\Exception $e) {
                $results[$recipient['email']] = 'failed: ' . $e->getMessage();
            }
        }
        
        return $results;
    }
    
    public function getStatus(string $messageId): array
    {
        // Implement Mailchimp status check API
        return [
            'status' => 'sent',
            'timestamp' => now()->toIso8601String(),
        ];
    }
    
    public function getStatistics(string $campaignId): array
    {
        // Implement Mailchimp statistics API
        return [
            'sent' => 0,
            'delivered' => 0,
            'opened' => 0,
            'clicked' => 0,
        ];
    }
    
    public function testConnection(): bool
    {
        try {
            $response = Http::post($this->baseUrl . '/users/ping', [
                'key' => $this->credentials['api_key'],
            ]);
            
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
    
    public function supportsTracking(): bool
    {
        return true;
    }
    
    public function getWebhookUrl(): string
    {
        return route('email.webhooks.mailchimp');
    }
    
    public function validateWebhook(Request $request): bool
    {
        // Implement Mailchimp webhook signature validation
        return true;
    }
}
