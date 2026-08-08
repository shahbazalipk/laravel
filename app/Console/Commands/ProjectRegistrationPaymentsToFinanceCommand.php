<?php

namespace App\Console\Commands;

use App\Finance\Integrations\RegistrationPaymentProjector;
use App\Finance\Models\FinanceTransaction;
use App\Payments\Models\RegistrationPaymentEntry;
use App\Services\EventContextService;
use Illuminate\Console\Command;
use Throwable;

class ProjectRegistrationPaymentsToFinanceCommand extends Command
{
    protected $signature = 'finance:project-registration-payments
        {--event= : Limit to a specific event id}
        {--org= : Limit to a specific organization id}
        {--dry-run : Preview without writing finance transactions}';

    protected $description = 'Project existing registration payment entries into the Finance transactions ledger';

    public function handle(
        EventContextService $eventContext,
        RegistrationPaymentProjector $projector,
    ): int {
        if (! filter_var(config('modules.finance.enabled'), FILTER_VALIDATE_BOOLEAN)) {
            $this->error('Finance module is disabled. Set FINANCE_MODULE_ENABLED=true and clear config cache.');

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $eventFilter = $this->option('event') !== null ? (int) $this->option('event') : null;
        $orgFilter = $this->option('org') !== null ? (int) $this->option('org') : null;

        $projected = 0;
        $skipped = 0;
        $failed = 0;

        RegistrationPaymentEntry::withoutGlobalScopes()
            ->when($eventFilter, fn ($query) => $query->where('event_id', $eventFilter))
            ->when($orgFilter, fn ($query) => $query->where('org_id', $orgFilter))
            ->orderBy('id')
            ->chunkById(100, function ($entries) use ($eventContext, $projector, $dryRun, &$projected, &$skipped, &$failed): void {
                foreach ($entries as $entry) {
                    $sourceKey = 'registration-payment:'.$entry->public_id;
                    $exists = FinanceTransaction::withoutGlobalScopes()
                        ->where('event_id', $entry->event_id)
                        ->where('org_id', $entry->org_id)
                        ->where('source_key', $sourceKey)
                        ->exists();

                    if ($exists) {
                        $skipped++;
                        continue;
                    }

                    if ($dryRun) {
                        $this->line("Would project entry #{$entry->id} ({$entry->public_id}) {$entry->amount} {$entry->currency}");
                        $projected++;
                        continue;
                    }

                    try {
                        $eventContext->apply((int) $entry->event_id, (int) $entry->org_id);
                        $transaction = $projector->project($entry);
                        $this->info("Projected entry #{$entry->id} -> {$transaction->number}");
                        $projected++;
                    } catch (Throwable $exception) {
                        $failed++;
                        $this->error("Failed entry #{$entry->id}: {$exception->getMessage()}");
                    }
                }
            });

        $this->newLine();
        $this->info(($dryRun ? 'Would project' : 'Projected').": {$projected}");
        $this->info("Skipped existing: {$skipped}");
        if ($failed > 0) {
            $this->warn("Failed: {$failed}");

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
