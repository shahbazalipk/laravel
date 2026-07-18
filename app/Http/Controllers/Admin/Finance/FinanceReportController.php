<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Finance\Models\FinanceBill;
use App\Finance\Models\FinanceBudget;
use App\Finance\Models\FinanceInvoice;
use App\Finance\Models\FinanceRefund;
use App\Finance\Models\FinanceTransaction;
use App\Finance\Services\FinanceBudgetService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinanceReportController extends Controller
{
    public function __invoke(Request $request, FinanceBudgetService $budgets): View
    {
        $currency = strtoupper($request->string('currency', current_event_currency())->toString());
        $invoices = FinanceInvoice::query()->where('currency', $currency)->get();
        $bills = FinanceBill::query()->where('currency', $currency)->get();
        $refunds = FinanceRefund::query()->where('currency', $currency)->get();
        $transactions = FinanceTransaction::query()
            ->where('currency', $currency)
            ->orderBy('occurred_at')
            ->get();

        $summary = [
            'invoiced' => $invoices->sum('total_amount'),
            'received' => $invoices->sum('paid_amount'),
            'billed' => $bills->sum('total_amount'),
            'paid' => $bills->sum('paid_amount'),
            'refunded' => $refunds->sum('amount'),
            'profit_loss' => $transactions->reduce(
                fn (string $carry, FinanceTransaction $transaction) => $transaction->direction->value === 'incoming'
                    ? bcadd($carry, $transaction->amount, 4)
                    : bcsub($carry, $transaction->amount, 4),
                '0.0000',
            ),
        ];

        $receivablesAging = $this->aging(
            $invoices->filter(fn ($invoice) => bccomp($invoice->total_amount, $invoice->paid_amount, 4) === 1),
            fn ($invoice) => bcsub($invoice->total_amount, $invoice->paid_amount, 4),
        );
        $payablesAging = $this->aging(
            $bills->filter(fn ($bill) => bccomp($bill->total_amount, $bill->paid_amount, 4) === 1),
            fn ($bill) => bcsub($bill->total_amount, $bill->paid_amount, 4),
        );
        $cashFlow = $transactions->groupBy(fn ($transaction) => $transaction->occurred_at->format('Y-m'))
            ->map(fn ($month) => [
                'incoming' => $month->where('direction.value', 'incoming')->sum('amount'),
                'outgoing' => $month->where('direction.value', 'outgoing')->sum('amount'),
            ]);
        $budgetPerformance = FinanceBudget::query()
            ->where('currency', $currency)
            ->get()
            ->map(fn (FinanceBudget $budget) => [
                'budget' => $budget,
                'summary' => $budgets->summary($budget),
            ]);
        $availableCurrencies = collect([
            ...FinanceTransaction::query()->distinct()->pluck('currency'),
            ...FinanceInvoice::query()->distinct()->pluck('currency'),
            ...FinanceBill::query()->distinct()->pluck('currency'),
            $currency,
        ])->unique()->sort()->values();

        return view('admin.finance.reports.index', compact(
            'currency',
            'summary',
            'receivablesAging',
            'payablesAging',
            'cashFlow',
            'budgetPerformance',
            'availableCurrencies',
        ));
    }

    private function aging($records, callable $outstanding): array
    {
        $buckets = ['not_due' => '0.0000', '1_30' => '0.0000', '31_60' => '0.0000', '61_90' => '0.0000', 'over_90' => '0.0000'];
        foreach ($records as $record) {
            $days = $record->due_date ? (int) $record->due_date->diffInDays(today(), false) : -1;
            $bucket = match (true) {
                $days <= 0 => 'not_due',
                $days <= 30 => '1_30',
                $days <= 60 => '31_60',
                $days <= 90 => '61_90',
                default => 'over_90',
            };
            $buckets[$bucket] = bcadd($buckets[$bucket], $outstanding($record), 4);
        }

        return $buckets;
    }
}
