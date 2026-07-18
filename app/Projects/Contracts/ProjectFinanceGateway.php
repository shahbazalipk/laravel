<?php

namespace App\Projects\Contracts;

use App\Projects\Data\ProjectFinanceSummary;
use App\Projects\Models\Project;
use App\Projects\Models\ProjectContract;
use App\Projects\Models\ProjectTask;

interface ProjectFinanceGateway
{
    public function summary(Project $project): ProjectFinanceSummary;

    public function linkExpense(Project $project, string $expensePublicId, ?ProjectTask $task = null): void;

    public function linkBudget(Project $project, string $budgetPublicId): void;

    public function linkBill(ProjectContract $contract, string $billPublicId): void;

    public function linkVendor(ProjectContract $contract, string $vendorPublicId): void;
}
