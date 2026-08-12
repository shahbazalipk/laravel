<?php

namespace App\Registration\Exceptions;

use App\Models\Event;
use App\Models\EventUrl;
use Exception;

class RegistrationUrlClosedException extends Exception
{
    public function __construct(
        public readonly ?Event $event,
        public readonly ?EventUrl $eventUrl = null,
        ?string $message = null,
    ) {
        parent::__construct($message ?? $this->defaultMessage());
    }

    public function closedMessage(): string
    {
        if ($this->eventUrl?->registration_closed_message) {
            return $this->eventUrl->registration_closed_message;
        }

        if ($this->event?->closed_message) {
            return $this->event->closed_message;
        }

        return $this->getMessage() ?: 'Registration for this link is now closed.';
    }

    private function defaultMessage(): string
    {
        if ($this->eventUrl && ! $this->eventUrl->is_active) {
            return 'Registration for this link is currently unavailable.';
        }

        if ($this->eventUrl?->isExpired()) {
            return 'Registration for this link has expired.';
        }

        return 'Registration for this link is now closed.';
    }
}
