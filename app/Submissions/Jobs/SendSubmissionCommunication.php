<?php

namespace App\Submissions\Jobs;

use App\Services\ProviderManager;
use App\Submissions\Models\CommunicationLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class SendSubmissionCommunication implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @param array<string, mixed> $context */
    public function __construct(
        public int $eventId,
        public int $orgId,
        public string $recipient,
        public string $subject,
        public string $html,
        public string $text,
        public array $context = [],
    ) {}

    public function handle(ProviderManager $providers): void
    {
        config(['event.event_id' => $this->eventId, 'event.org_id' => $this->orgId]);
        $log = CommunicationLog::query()->create([
            'recipient' => $this->recipient,
            'subject' => $this->subject,
            'body' => $this->html,
            'channel' => 'email',
            'status' => 'sending',
            'metadata' => $this->context,
        ]);

        try {
            $providers->getProvider()->send($this->recipient, $this->subject, $this->html, $this->text);
            $log->update(['status' => 'sent', 'sent_at' => now()]);
        } catch (Throwable $exception) {
            $log->update(['status' => 'failed', 'error' => $exception->getMessage()]);
            throw $exception;
        }
    }
}
