<?php

namespace App\Finance\Integrations;

use App\Finance\Models\FinanceBill;
use App\Finance\Models\FinanceBudget;
use App\Finance\Models\FinanceExpense;
use App\Finance\Models\FinanceVendor;
use App\Finance\Services\MoneyService;
use App\Projects\Contracts\ProjectFinanceGateway;
use App\Projects\Data\ProjectFinanceSummary;
use App\Projects\Events\ProjectFinancialResourceLinked;
use App\Projects\Models\Project;
use App\Projects\Models\ProjectContract;
use App\Projects\Models\ProjectTask;
use App\Shared\Integration\Models\ResourceLink;
use App\Shared\Integration\ResourceLinker;
use App\Shared\Integration\ResourceReference;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProjectFinanceIntegration implements ProjectFinanceGateway
{
    public function __construct(
        private readonly ResourceLinker $links,
        private readonly MoneyService $money,
    ) {}

    public function summary(Project $project): ProjectFinanceSummary
    {
        $targets = ResourceLink::query()
            ->where('source_module', 'projects')
            ->where('source_type', 'project')
            ->where('source_public_id', $project->public_id)
            ->where('target_module', 'finance')
            ->get()
            ->groupBy('target_type');
        $expenseIds = $targets->get('expense', collect())->pluck('target_public_id');
        $budgetIds = $targets->get('budget', collect())->pluck('target_public_id');
        $billIds = $targets->get('bill', collect())->pluck('target_public_id');
        $currency = $project->currency ?: current_event()->currency;

        $expenses = FinanceExpense::query()->whereIn('public_id', $expenseIds)->where('currency', $currency);
        $bills = FinanceBill::query()->whereIn('public_id', $billIds)->where('currency', $currency);
        $budget = $budgetIds->isNotEmpty()
            ? $this->money->normalize(FinanceBudget::query()
                ->whereIn('public_id', $budgetIds)
                ->where('currency', $currency)
                ->sum('planned_expense'))
            : $this->money->normalize($project->budget_amount ?? 0);
        $approvedExpenses = $this->money->normalize((clone $expenses)
            ->whereIn('status', ['approved', 'scheduled_for_payment', 'partially_paid', 'paid', 'overdue'])
            ->sum('approved_amount'));
        $paidExpenses = $this->money->normalize((clone $expenses)->sum('paid_amount'));
        $billCommitments = $this->money->normalize((clone $bills)
            ->where('approval_status', 'approved')
            ->sum('total_amount'));
        $linkedContractIds = ResourceLink::query()
            ->where('source_module', 'projects')
            ->where('source_type', 'contract')
            ->where('target_module', 'finance')
            ->where('target_type', 'bill')
            ->pluck('source_public_id');
        $contractCommitments = $this->money->normalize($project->contracts()
            ->whereIn('status', ['approved', 'active', 'signed'])
            ->whereNotIn('public_id', $linkedContractIds)
            ->where('currency', $currency)
            ->sum('value'));
        $committed = bcadd(bcadd($approvedExpenses, $billCommitments, 4), $contractCommitments, 4);

        return new ProjectFinanceSummary(
            budget: $budget,
            approvedExpenses: $approvedExpenses,
            paidExpenses: $paidExpenses,
            committedExpenses: $committed,
            remainingBudget: bcsub($budget, $committed, 4),
            pendingExpenseRequests: (clone $expenses)->whereIn('status', ['draft', 'pending_approval'])->count(),
            currency: $currency,
        );
    }

    public function linkExpense(Project $project, string $expensePublicId, ?ProjectTask $task = null): void
    {
        $expense = FinanceExpense::query()->where('public_id', $expensePublicId)->firstOrFail();
        $this->assertCurrency($project, $expense->currency);
        $this->link($this->project($project), $this->finance('expense', $expense->public_id), 'incurs');
        if ($task) {
            if ((int) $task->project_id !== (int) $project->id) {
                throw (new ModelNotFoundException)->setModel(ProjectTask::class, [$task->getRouteKey()]);
            }
            $this->link($this->task($task), $this->finance('expense', $expense->public_id), 'incurs');
        }
    }

    public function linkBudget(Project $project, string $budgetPublicId): void
    {
        $budget = FinanceBudget::query()->where('public_id', $budgetPublicId)->firstOrFail();
        $this->assertCurrency($project, $budget->currency);
        $this->link($this->project($project), $this->finance('budget', $budget->public_id), 'funded_by');
    }

    public function linkBill(ProjectContract $contract, string $billPublicId): void
    {
        $bill = FinanceBill::query()->where('public_id', $billPublicId)->firstOrFail();
        $this->assertCurrency($contract->project, $bill->currency);
        DB::transaction(function () use ($contract, $bill): void {
            $this->link($this->contract($contract), $this->finance('bill', $bill->public_id), 'payable_as');
            $this->link($this->project($contract->project), $this->finance('bill', $bill->public_id), 'incurs');
        });
    }

    public function linkVendor(ProjectContract $contract, string $vendorPublicId): void
    {
        $vendor = FinanceVendor::query()->where('public_id', $vendorPublicId)->firstOrFail();
        $this->link($this->contract($contract), $this->finance('vendor', $vendor->public_id), 'counterparty');
    }

    private function link(ResourceReference $source, ResourceReference $target, string $relationship): void
    {
        $this->links->link($source, $target, $relationship);
        if ($source->type === 'project') {
            ProjectFinancialResourceLinked::dispatch(
                (int) config('event.event_id'),
                (int) config('event.org_id'),
                $source->publicId,
                $target->type,
                $target->publicId,
                $relationship,
            );
        }
    }

    private function assertCurrency(Project $project, string $currency): void
    {
        if ($project->currency && strtoupper($project->currency) !== strtoupper($currency)) {
            throw ValidationException::withMessages([
                'resource_public_id' => 'The financial resource currency must match the project currency.',
            ]);
        }
    }

    private function project(Project $project): ResourceReference
    {
        return new ResourceReference('projects', 'project', $project->public_id);
    }

    private function task(ProjectTask $task): ResourceReference
    {
        return new ResourceReference('projects', 'task', $task->public_id);
    }

    private function contract(ProjectContract $contract): ResourceReference
    {
        return new ResourceReference('projects', 'contract', $contract->public_id);
    }

    private function finance(string $type, string $publicId): ResourceReference
    {
        return new ResourceReference('finance', $type, $publicId);
    }
}
