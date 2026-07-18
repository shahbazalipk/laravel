<?php

namespace App\Http\Controllers\Admin\Projects;

use App\Finance\Models\FinanceBill;
use App\Finance\Models\FinanceBudget;
use App\Finance\Models\FinanceExpense;
use App\Finance\Models\FinanceVendor;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Projects\LinkProjectFinanceResourceRequest;
use App\Http\Requests\Admin\Projects\StoreProjectContractRequest;
use App\Http\Requests\Admin\Projects\TransitionProjectContractRequest;
use App\Projects\Contracts\ProjectFinanceGateway;
use App\Projects\Models\Project;
use App\Projects\Models\ProjectContract;
use App\Projects\Models\ProjectTask;
use App\Projects\Services\ProjectContractService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProjectFinanceController extends Controller
{
    public function show(Project $project, ProjectFinanceGateway $finance): View
    {
        $project->load(['contracts.task', 'tasks']);
        $summary = $finance->summary($project);
        $expenses = FinanceExpense::query()->latest()->limit(100)->get();
        $budgets = FinanceBudget::query()->where('status', 'active')->latest()->get();
        $vendors = FinanceVendor::query()->where('is_active', true)->orderBy('name')->get();
        $bills = FinanceBill::query()->latest()->limit(100)->get();

        return view('admin.projects.finance.show', compact(
            'project',
            'summary',
            'expenses',
            'budgets',
            'vendors',
            'bills',
        ));
    }

    public function link(
        LinkProjectFinanceResourceRequest $request,
        Project $project,
        ProjectFinanceGateway $finance,
    ): RedirectResponse {
        $data = $request->validated();
        if ($data['resource_type'] === 'budget') {
            $finance->linkBudget($project, $data['resource_public_id']);
        } else {
            $task = isset($data['task_id'])
                ? ProjectTask::query()->where('project_id', $project->id)->findOrFail($data['task_id'])
                : null;
            $finance->linkExpense($project, $data['resource_public_id'], $task);
        }

        return back()->with('success', 'Financial resource linked to the project.');
    }

    public function contract(
        StoreProjectContractRequest $request,
        Project $project,
        ProjectContractService $service,
    ): RedirectResponse {
        $service->create($project, $request->validated());

        return back()->with('success', 'Contract created.');
    }

    public function transition(
        TransitionProjectContractRequest $request,
        Project $project,
        ProjectContract $contract,
        ProjectContractService $service,
    ): RedirectResponse {
        abort_unless((int) $contract->project_id === (int) $project->id, 404);
        $service->transition($contract, $request->string('status')->value());

        return back()->with('success', 'Contract status updated.');
    }
}
