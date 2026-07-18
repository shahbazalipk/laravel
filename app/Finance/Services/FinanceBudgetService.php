<?php

namespace App\Finance\Services;

use App\Finance\Models\FinanceBill;
use App\Finance\Models\FinanceBudget;
use App\Finance\Models\FinanceBudgetAlert;
use App\Finance\Models\FinanceExpense;
use App\Finance\Models\FinanceInvoice;
use App\Shared\Audit\AuditLogger;
use App\Shared\Notifications\NotificationService;

class FinanceBudgetService
{
    public function __construct(
        private readonly MoneyService $money,
        private readonly FinanceNumberService $numbers,
        private readonly NotificationService $notifications,
        private readonly AuditLogger $audit,
    ) {}

    public function create(array $data): FinanceBudget
    {
        $budget = FinanceBudget::query()->create([
            ...$data,
            'number' => $this->numbers->next('budget', 'BDG'),
            'currency' => $this->money->currency($data['currency']),
            'planned_income' => $this->money->nonNegative($data['planned_income'] ?? 0),
            'planned_expense' => $this->money->positive($data['planned_expense']),
            'warning_threshold' => (int) ($data['warning_threshold'] ?? 75),
            'critical_threshold' => (int) ($data['critical_threshold'] ?? 90),
            'owner_admin_id' => $data['owner_admin_id'] ?? session('admin_id'),
            'status' => $data['status'] ?? 'active',
        ]);

        $this->audit->record('finance', 'budget.created', $budget, after: $budget->only([
            'number', 'name', 'type', 'planned_income', 'planned_expense', 'currency',
        ]));

        return $budget;
    }

    /** @return array{actual_income:string,actual_expense:string,committed_expense:string,remaining:string,utilization_percent:int} */
    public function summary(FinanceBudget $budget): array
    {
        $expenseQuery = FinanceExpense::query()
            ->whereBetween('expense_date', [$budget->period_start, $budget->period_end])
            ->where('currency', $budget->currency)
            ->when($budget->category_id, fn ($query) => $query->where('category_id', $budget->category_id));
        $billQuery = FinanceBill::query()
            ->whereBetween('bill_date', [$budget->period_start, $budget->period_end])
            ->where('currency', $budget->currency)
            ->when($budget->category_id, fn ($query) => $query->where('category_id', $budget->category_id));

        $actualExpense = bcadd(
            $this->money->normalize($expenseQuery->sum('paid_amount')),
            $this->money->normalize((clone $billQuery)->sum('paid_amount')),
            4,
        );
        $committedExpense = bcadd(
            $this->money->normalize($expenseQuery->sum('approved_amount')),
            $this->money->normalize((clone $billQuery)->where('approval_status', 'approved')->sum('total_amount')),
            4,
        );
        $actualIncome = $this->money->normalize(
            FinanceInvoice::query()
                ->whereBetween('invoice_date', [$budget->period_start, $budget->period_end])
                ->where('currency', $budget->currency)
                ->sum('paid_amount')
        );
        $utilization = bccomp($budget->planned_expense, '0', 4) === 1
            ? (int) min(999, round((float) bcdiv(bcmul($committedExpense, '100', 4), $budget->planned_expense, 4)))
            : 0;

        return [
            'actual_income' => $actualIncome,
            'actual_expense' => $actualExpense,
            'committed_expense' => $committedExpense,
            'remaining' => bcsub($budget->planned_expense, $committedExpense, 4),
            'utilization_percent' => $utilization,
        ];
    }

    public function evaluateAlerts(FinanceBudget $budget): ?FinanceBudgetAlert
    {
        $summary = $this->summary($budget);
        $type = match (true) {
            $summary['utilization_percent'] > 100 => 'exceeded',
            $summary['utilization_percent'] >= $budget->critical_threshold => 'critical',
            $summary['utilization_percent'] >= $budget->warning_threshold => 'warning',
            default => null,
        };

        if ($type === null) {
            return null;
        }

        $alert = FinanceBudgetAlert::query()->firstOrCreate(
            ['budget_id' => $budget->id, 'type' => $type],
            [
                'utilization_percent' => $summary['utilization_percent'],
                'actual_amount' => $summary['committed_expense'],
                'budget_amount' => $budget->planned_expense,
                'message' => "{$budget->name} is {$summary['utilization_percent']}% utilized.",
                'triggered_at' => now(),
            ],
        );

        if ($alert->wasRecentlyCreated && $budget->owner_admin_id) {
            $this->notifications->send(
                (int) $budget->owner_admin_id,
                'finance',
                'budget.threshold',
                'Budget threshold reached',
                $alert->message,
                FinanceBudget::class,
                $budget->public_id,
                ['alert_type' => $type, 'utilization_percent' => $summary['utilization_percent']],
                "finance-budget-alert:{$alert->public_id}",
            );
        }

        return $alert;
    }
}
