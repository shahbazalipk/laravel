<?php

namespace App\Finance\Listeners;

use App\Finance\Integrations\RegistrationPaymentProjector;
use App\Payments\Events\RegistrationPaymentRecorded;
use App\Payments\Models\RegistrationPaymentEntry;
use App\Services\EventContextService;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;

class ProjectRegistrationPaymentToFinance implements ShouldQueueAfterCommit
{
    public function __construct(
        private EventContextService $eventContext,
        private RegistrationPaymentProjector $projector,
    ) {}

    public function handle(RegistrationPaymentRecorded $event): void
    {
        if (config('modules.finance.enabled') !== true) {
            return;
        }

        $this->eventContext->apply($event->eventId, $event->organizationId);

        $entry = RegistrationPaymentEntry::query()
            ->where('public_id', $event->paymentEntryPublicId)
            ->firstOrFail();

        $this->projector->project($entry);
    }
}
