<?php

namespace App\Projects\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProjectFinancialResourceLinked implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int $eventId,
        public readonly int $organizationId,
        public readonly string $projectPublicId,
        public readonly string $resourceType,
        public readonly string $resourcePublicId,
        public readonly string $relationship,
    ) {}
}
