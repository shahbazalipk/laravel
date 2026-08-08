<?php

namespace App\Finance\Listeners;

use App\Finance\Integrations\RegistrationPaymentProjector;
use App\Payments\Events\RegistrationPaymentRecorded;
use App\Payments\Models\RegistrationPaymentEntry;
use App\Services\EventContextService;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Support\Facades\Log;

class ProjectRegistrationPaymentToFinance implements ShouldQueueAfterCommit
{
    public function __construct(
        private EventContextService $eventContext,
        private RegistrationPaymentProjector $projector,
    ) {}

    public function handle(RegistrationPaymentRecorded $event): void
    {
        if (! filter_var(config('modules.finance.enabled'), FILTER_VALIDATE_BOOLEAN)) {
            Log::info('Skipping registration payment finance projection because the Finance module is disabled.', [
                'payment_entry_public_id' => $event->paymentEntryPublicId,
                'event_id' => $event->eventId,
                'org_id' => $event->organizationId,
                'finance_enabled_raw' => config('modules.finance.enabled'),
            ]);

            return;
        }

        $this->eventContext->apply($event->eventId, $event->organizationId);

        $entry = RegistrationPaymentEntry::query()
            ->where('public_id', $event->paymentEntryPublicId)
            ->firstOrFail();

        $transaction = $this->projector->project($entry);

        Log::info('Projected registration payment into finance ledger.', [
            'payment_entry_public_id' => $entry->public_id,
            'finance_transaction_id' => $transaction->id,
            'finance_transaction_number' => $transaction->number,
            'amount' => $transaction->amount,
            'currency' => $transaction->currency,
            'event_id' => $event->eventId,
            'org_id' => $event->organizationId,
        ]);
    }
}
