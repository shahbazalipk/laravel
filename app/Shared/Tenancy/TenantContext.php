<?php

namespace App\Shared\Tenancy;

use RuntimeException;

final readonly class TenantContext
{
    public function __construct(
        public int $eventId,
        public int $organizationId,
    ) {
        if ($eventId < 1 || $organizationId < 1) {
            throw new RuntimeException('A valid event and organization context is required.');
        }
    }

    public static function fromConfig(): self
    {
        $eventId = (int) config('event.event_id');
        $organizationId = (int) config('event.org_id');

        if ($eventId < 1 || $organizationId < 1) {
            throw new RuntimeException('Event context is missing. Open this event from the organization portal.');
        }

        return new self($eventId, $organizationId);
    }

    public function assertMatches(int $eventId, int $organizationId): void
    {
        if ($eventId !== $this->eventId || $organizationId !== $this->organizationId) {
            throw new RuntimeException('The selected record does not belong to the active event.');
        }
    }
}
