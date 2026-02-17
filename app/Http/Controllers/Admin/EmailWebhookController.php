<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\TrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmailWebhookController extends Controller
{
    protected TrackingService $trackingService;
    
    public function __construct(TrackingService $trackingService)
    {
        $this->trackingService = $trackingService;
    }
    
    /**
     * Handle Infobip webhook events
     */
    public function infobip(Request $request)
    {
        // Validate webhook signature
        if (!$this->validateInfobipSignature($request)) {
            Log::warning('Invalid Infobip webhook signature', [
                'ip' => $request->ip(),
                'headers' => $request->headers->all(),
            ]);
            return response()->json(['error' => 'Invalid signature'], 401);
        }
        
        $events = $request->input('results', []);
        
        foreach ($events as $event) {
            $messageId = $event['messageId'] ?? null;
            $eventType = $event['status']['groupName'] ?? null;
            
            if (!$messageId) {
                continue;
            }
            
            try {
                switch ($eventType) {
                    case 'DELIVERED':
                        $this->trackingService->logDelivered($messageId);
                        break;
                        
                    case 'OPENED':
                        $this->trackingService->logOpened($messageId);
                        break;
                        
                    case 'CLICKED':
                        $url = $event['url'] ?? '';
                        $this->trackingService->logClicked($messageId, $url);
                        break;
                        
                    case 'BOUNCED':
                    case 'REJECTED':
                        $reason = $event['status']['description'] ?? 'Unknown';
                        $this->trackingService->logBounced($messageId, $reason);
                        break;
                        
                    case 'FAILED':
                        $error = $event['error']['description'] ?? 'Unknown error';
                        $this->trackingService->logFailed($messageId, $error);
                        break;
                }
            } catch (\Exception $e) {
                Log::error('Error processing Infobip webhook event', [
                    'message_id' => $messageId,
                    'event_type' => $eventType,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        return response()->json(['status' => 'ok']);
    }
    
    /**
     * Handle Mailchimp webhook events
     */
    public function mailchimp(Request $request)
    {
        // Validate webhook signature
        if (!$this->validateMailchimpSignature($request)) {
            Log::warning('Invalid Mailchimp webhook signature', [
                'ip' => $request->ip(),
                'headers' => $request->headers->all(),
            ]);
            return response()->json(['error' => 'Invalid signature'], 401);
        }
        
        $eventType = $request->input('type');
        $messageId = $request->input('msg.metadata.message_id') ?? $request->input('msg._id');
        
        if (!$messageId) {
            return response()->json(['status' => 'ok']);
        }
        
        try {
            switch ($eventType) {
                case 'send':
                    $this->trackingService->logDelivered($messageId);
                    break;
                    
                case 'open':
                    $this->trackingService->logOpened($messageId);
                    break;
                    
                case 'click':
                    $url = $request->input('msg.clicks.0.url', '');
                    $this->trackingService->logClicked($messageId, $url);
                    break;
                    
                case 'hard_bounce':
                case 'soft_bounce':
                    $reason = $request->input('msg.bounce_description', 'Unknown');
                    $this->trackingService->logBounced($messageId, $reason);
                    break;
                    
                case 'reject':
                case 'spam':
                    $reason = $request->input('msg.reject.reason', 'Rejected');
                    $this->trackingService->logBounced($messageId, $reason);
                    break;
            }
        } catch (\Exception $e) {
            Log::error('Error processing Mailchimp webhook event', [
                'message_id' => $messageId,
                'event_type' => $eventType,
                'error' => $e->getMessage(),
            ]);
        }
        
        return response()->json(['status' => 'ok']);
    }
    
    /**
     * Validate Infobip webhook signature
     * 
     * @param Request $request
     * @return bool
     */
    protected function validateInfobipSignature(Request $request): bool
    {
        // Get webhook secret from config or database
        $secret = config('services.infobip.webhook_secret');
        
        if (!$secret) {
            // If no secret configured, skip validation (not recommended for production)
            return true;
        }
        
        $signature = $request->header('X-Infobip-Signature');
        
        if (!$signature) {
            return false;
        }
        
        $payload = $request->getContent();
        $expectedSignature = hash_hmac('sha256', $payload, $secret);
        
        return hash_equals($expectedSignature, $signature);
    }
    
    /**
     * Validate Mailchimp webhook signature
     * 
     * @param Request $request
     * @return bool
     */
    protected function validateMailchimpSignature(Request $request): bool
    {
        // Get webhook secret from config or database
        $secret = config('services.mailchimp.webhook_secret');
        
        if (!$secret) {
            // If no secret configured, skip validation (not recommended for production)
            return true;
        }
        
        $signature = $request->header('X-Mandrill-Signature');
        
        if (!$signature) {
            return false;
        }
        
        // Mailchimp uses a specific signature format
        $url = $request->fullUrl();
        $params = $request->all();
        
        ksort($params);
        
        $signedData = $url;
        foreach ($params as $key => $value) {
            $signedData .= $key . $value;
        }
        
        $expectedSignature = base64_encode(hash_hmac('sha1', $signedData, $secret, true));
        
        return hash_equals($expectedSignature, $signature);
    }
}
