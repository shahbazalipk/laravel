<?php

namespace App\Finance\Services;

use App\Finance\Enums\FinanceTransactionDirection;
use App\Finance\Enums\FinanceTransactionType;
use App\Finance\Models\FinanceAccount;
use App\Finance\Models\FinanceAdjustment;
use App\Finance\Models\FinanceReconciliation;
use App\Finance\Models\FinanceReconciliationMatch;
use App\Finance\Models\FinanceStatementEntry;
use App\Finance\Models\FinanceTransaction;
use App\Shared\Audit\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FinanceReconciliationService
{
    public function __construct(
        private readonly MoneyService $money,
        private readonly FinanceNumberService $numbers,
        private readonly RecordFinanceTransaction $transactions,
        private readonly AuditLogger $audit,
    ) {}

    public function create(array $data): FinanceReconciliation
    {
        $account = FinanceAccount::query()->where('is_active', true)->findOrFail($data['account_id']);

        return FinanceReconciliation::query()->create([
            'number' => $this->numbers->next('reconciliation', 'REC'),
            'account_id' => $account->id,
            'period_start' => $data['period_start'],
            'period_end' => $data['period_end'],
            'statement_opening_balance' => $this->money->normalize($data['statement_opening_balance']),
            'statement_closing_balance' => $this->money->normalize($data['statement_closing_balance']),
            'currency' => $account->currency,
            'status' => 'in_progress',
            'notes' => $data['notes'] ?? null,
            'created_by' => session('admin_id'),
        ]);
    }

    /** @return list<FinanceReconciliationMatch> */
    public function suggest(FinanceStatementEntry $entry): array
    {
        if ($entry->status === 'matched') {
            return [];
        }

        $transactions = FinanceTransaction::query()
            ->where('account_id', $entry->account_id)
            ->where('reconciliation_status', 'unreconciled')
            ->where('direction', $entry->direction)
            ->where('currency', $entry->currency)
            ->where('amount', $entry->amount)
            ->whereBetween('occurred_at', [
                $entry->transaction_date->subDays(3)->startOfDay(),
                $entry->transaction_date->addDays(3)->endOfDay(),
            ])
            ->limit(10)
            ->get();

        return $transactions->map(function (FinanceTransaction $transaction) use ($entry): FinanceReconciliationMatch {
            $days = abs($entry->transaction_date->diffInDays($transaction->occurred_at, false));
            $score = 50 + max(0, 30 - ($days * 10));
            $reasons = ['exact amount and direction'];
            if ($entry->reference && $transaction->reference
                && strcasecmp($entry->reference, $transaction->reference) === 0) {
                $score += 20;
                $reasons[] = 'exact reference';
            }

            return FinanceReconciliationMatch::query()->updateOrCreate(
                [
                    'statement_entry_id' => $entry->id,
                    'transaction_id' => $transaction->id,
                ],
                [
                    'confidence_score' => min(100, $score),
                    'match_reasons' => $reasons,
                    'status' => 'suggested',
                ],
            );
        })->all();
    }

    public function confirm(
        FinanceReconciliation $reconciliation,
        FinanceReconciliationMatch $match,
    ): FinanceReconciliationMatch {
        return DB::transaction(function () use ($reconciliation, $match): FinanceReconciliationMatch {
            $match->loadMissing(['statementEntry', 'transaction']);
            $entry = $match->statementEntry;
            $transaction = $match->transaction;

            if ((int) $entry->account_id !== (int) $reconciliation->account_id
                || $entry->transaction_date->lt($reconciliation->period_start)
                || $entry->transaction_date->gt($reconciliation->period_end)) {
                throw ValidationException::withMessages(['match' => 'The entry is outside this reconciliation.']);
            }
            if ($transaction->reconciliation_status !== 'unreconciled') {
                throw ValidationException::withMessages(['match' => 'The transaction is already reconciled.']);
            }

            $match->forceFill([
                'reconciliation_id' => $reconciliation->id,
                'status' => 'confirmed',
                'confirmed_by' => session('admin_id'),
                'confirmed_at' => now(),
            ])->save();
            $entry->forceFill(['status' => 'matched'])->save();
            $transaction->forceFill([
                'reconciliation_id' => $reconciliation->id,
                'reconciliation_status' => 'reconciled',
                'reconciled_by' => session('admin_id'),
                'reconciled_at' => now(),
            ])->save();

            $this->audit->record('finance', 'reconciliation.match-confirmed', $reconciliation, after: [
                'statement_entry' => $entry->public_id,
                'transaction' => $transaction->public_id,
                'confidence_score' => $match->confidence_score,
            ]);

            return $match->fresh();
        });
    }

    public function addAdjustment(FinanceReconciliation $reconciliation, array $data): FinanceAdjustment
    {
        if ($reconciliation->status === 'completed') {
            throw ValidationException::withMessages(['type' => 'Completed reconciliations cannot be adjusted.']);
        }

        return DB::transaction(function () use ($reconciliation, $data): FinanceAdjustment {
            $amount = $this->money->positive($data['amount']);
            $adjustment = FinanceAdjustment::query()->create([
                'number' => $this->numbers->next('adjustment', 'ADJ'),
                'reconciliation_id' => $reconciliation->id,
                'type' => $data['type'],
                'direction' => $data['direction'],
                'amount' => $amount,
                'currency' => $reconciliation->currency,
                'reason' => $data['reason'],
                'created_by' => session('admin_id'),
            ]);
            $transaction = $this->transactions->record([
                'account_id' => $reconciliation->account_id,
                'direction' => $data['direction'],
                'type' => FinanceTransactionType::Adjustment->value,
                'amount' => $amount,
                'currency' => $reconciliation->currency,
                'source_type' => 'finance_adjustment',
                'source_id' => $adjustment->id,
                'source_public_id' => $adjustment->public_id,
                'source_key' => 'finance-adjustment:'.$adjustment->public_id,
                'occurred_at' => $data['occurred_at'] ?? now(),
                'description' => $data['reason'],
            ]);
            $adjustment->forceFill(['transaction_id' => $transaction->id])->save();

            $this->audit->record('finance', 'reconciliation.adjustment-created', $reconciliation, after: [
                'adjustment' => $adjustment->public_id,
                'type' => $adjustment->type,
                'amount' => $amount,
                'direction' => $adjustment->direction,
            ]);

            return $adjustment;
        });
    }

    public function complete(FinanceReconciliation $reconciliation): FinanceReconciliation
    {
        $incoming = FinanceTransaction::query()
            ->where('account_id', $reconciliation->account_id)
            ->whereBetween('occurred_at', [$reconciliation->period_start->startOfDay(), $reconciliation->period_end->endOfDay()])
            ->where('direction', FinanceTransactionDirection::Incoming->value)
            ->sum('amount');
        $outgoing = FinanceTransaction::query()
            ->where('account_id', $reconciliation->account_id)
            ->whereBetween('occurred_at', [$reconciliation->period_start->startOfDay(), $reconciliation->period_end->endOfDay()])
            ->where('direction', FinanceTransactionDirection::Outgoing->value)
            ->sum('amount');
        $calculated = bcsub(
            bcadd($reconciliation->statement_opening_balance, $this->money->normalize($incoming), 4),
            $this->money->normalize($outgoing),
            4,
        );
        $difference = bcsub($reconciliation->statement_closing_balance, $calculated, 4);

        if (bccomp(ltrim($difference, '-'), '0.0100', 4) === 1) {
            $reconciliation->forceFill([
                'calculated_closing_balance' => $calculated,
                'difference_amount' => $difference,
                'status' => 'adjustment_required',
            ])->save();
            throw ValidationException::withMessages([
                'reconciliation' => "A {$difference} {$reconciliation->currency} difference remains.",
            ]);
        }

        $reconciliation->forceFill([
            'calculated_closing_balance' => $calculated,
            'difference_amount' => $difference,
            'status' => 'completed',
            'completed_by' => session('admin_id'),
            'completed_at' => now(),
        ])->save();

        $this->audit->record('finance', 'reconciliation.completed', $reconciliation, after: [
            'calculated_closing_balance' => $calculated,
            'difference_amount' => $difference,
        ]);

        return $reconciliation;
    }
}
