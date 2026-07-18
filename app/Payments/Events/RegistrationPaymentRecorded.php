<?php

namespace App\Payments\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

final readonly class RegistrationPaymentRecorded implements ShouldDispatchAfterCommit
{
    public function __construct(
        public string $paymentEntryPublicId,
        public int $eventId,
        public int $organizationId,
    ) {}
}
