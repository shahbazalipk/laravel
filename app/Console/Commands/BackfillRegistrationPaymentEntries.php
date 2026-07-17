<?php

namespace App\Console\Commands;

use App\Models\Registration;
use App\Payments\Enums\PaymentEntryStatus;
use App\Payments\Enums\PaymentEntryType;
use App\Payments\Models\RegistrationPaymentEntry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BackfillRegistrationPaymentEntries extends Command
{
    protected $signature = 'payments:backfill-paid-registrations {--dry-run : Preview without writing}';

    protected $description = 'Create opening payment ledger entries for existing paid registrations';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $created = 0;
        $skipped = 0;

        Registration::withoutGlobalScopes()
            ->where('payment_status', 'paid')
            ->where('total_amount', '>', 0)
            ->orderBy('id')
            ->chunkById(100, function ($registrations) use ($dryRun, &$created, &$skipped): void {
                foreach ($registrations as $registration) {
                    $exists = RegistrationPaymentEntry::withoutGlobalScopes()
                        ->where('registration_id', $registration->id)
                        ->exists();

                    if ($exists) {
                        $skipped++;
                        continue;
                    }

                    if ($dryRun) {
                        $this->line("Would backfill registration #{$registration->id} ({$registration->registration_number})");
                        $created++;
                        continue;
                    }

                    DB::transaction(function () use ($registration, &$created): void {
                        RegistrationPaymentEntry::withoutGlobalScopes()->create([
                            'public_id' => (string) Str::uuid(),
                            'event_id' => $registration->event_id,
                            'org_id' => $registration->org_id,
                            'registration_id' => $registration->id,
                            'type' => PaymentEntryType::Payment,
                            'status' => PaymentEntryStatus::Succeeded,
                            'amount' => $registration->total_amount,
                            'currency' => $registration->currency ?: 'AED',
                            'method' => $registration->payment_method,
                            'reference' => $registration->payment_reference,
                            'occurred_at' => $registration->payment_date ?: $registration->created_at ?: now(),
                            'notes' => 'Opening balance backfilled from legacy paid registration.',
                            'metadata' => [
                                'source' => 'legacy_paid_backfill',
                            ],
                            'created_at' => now(),
                        ]);
                        $created++;
                    });
                }
            });

        $this->info(($dryRun ? 'Would create ' : 'Created ').$created.' payment entries. Skipped '.$skipped.'.');

        return self::SUCCESS;
    }
}
