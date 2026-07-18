<?php

namespace App\Projects\Data;

final readonly class ProjectFinanceSummary
{
    public function __construct(
        public string $budget,
        public string $approvedExpenses,
        public string $paidExpenses,
        public string $committedExpenses,
        public string $remainingBudget,
        public int $pendingExpenseRequests,
        public string $currency,
    ) {}
}
