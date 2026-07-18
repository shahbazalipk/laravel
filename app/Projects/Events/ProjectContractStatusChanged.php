<?php

namespace App\Projects\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProjectContractStatusChanged implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int $eventId,
        public readonly int $organizationId,
        public readonly string $projectPublicId,
        public readonly string $contractPublicId,
        public readonly string $status,
        public readonly string $value,
        public readonly string $currency,
    ) {}
}
