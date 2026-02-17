<?php

namespace App\Contracts;

use Illuminate\Http\Request;

interface EmailProviderInterface
{
    /**
     * Send a single email
     * 
     * @param string $to Recipient email address
     * @param string $subject Email subject
     * @param string $html HTML content
     * @param string $text Plain text content
     * @param array $metadata Additional metadata (from, reply-to, etc.)
     * @return string Message ID from provider
     */
    public function send(string $to, string $subject, string $html, string $text, array $metadata): string;
    
    /**
     * Send batch of emails
     * 
     * @param array $recipients Array of recipient data
     * @param string $subject Email subject
     * @param string $html HTML content
     * @param string $text Plain text content
     * @return array Array of ['email' => 'message_id']
     */
    public function sendBatch(array $recipients, string $subject, string $html, string $text): array;
    
    /**
     * Get delivery status for a message
     * 
     * @param string $messageId
     * @return array ['status' => 'delivered|bounced|failed', 'timestamp' => ...]
     */
    public function getStatus(string $messageId): array;
    
    /**
     * Get campaign statistics
     * 
     * @param string $campaignId
     * @return array ['sent' => N, 'delivered' => N, 'opened' => N, 'clicked' => N]
     */
    public function getStatistics(string $campaignId): array;
    
    /**
     * Test connection to provider
     * 
     * @return bool
     */
    public function testConnection(): bool;
    
    /**
     * Check if provider supports tracking
     * 
     * @return bool
     */
    public function supportsTracking(): bool;
    
    /**
     * Get webhook URL for this provider
     * 
     * @return string
     */
    public function getWebhookUrl(): string;
    
    /**
     * Validate webhook request signature
     * 
     * @param Request $request
     * @return bool
     */
    public function validateWebhook(Request $request): bool;
}
