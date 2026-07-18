<?php

namespace App\Projects\Services;

use App\Projects\Contracts\ProjectFinanceGateway;
use App\Projects\Events\ProjectContractStatusChanged;
use App\Projects\Models\Project;
use App\Projects\Models\ProjectContract;
use App\Projects\Models\ProjectTask;
use App\Shared\Audit\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProjectContractService
{
    public function __construct(
        private readonly ProjectNumberService $numbers,
        private readonly ProjectFinanceGateway $finance,
        private readonly AuditLogger $audit,
    ) {}

    public function create(Project $project, array $data): ProjectContract
    {
        $task = isset($data['task_id'])
            ? ProjectTask::query()->where('project_id', $project->id)->findOrFail($data['task_id'])
            : null;

        return DB::transaction(function () use ($project, $task, $data): ProjectContract {
            $contract = ProjectContract::query()->create([
                'project_id' => $project->id,
                'task_id' => $task?->id,
                'number' => $this->numbers->next("contract:{$project->id}", 'CTR'),
                'title' => $data['title'],
                'type' => $data['type'],
                'counterparty_name' => $data['counterparty_name'],
                'value' => $data['value'],
                'currency' => strtoupper($data['currency']),
                'starts_on' => $data['starts_on'] ?? null,
                'ends_on' => $data['ends_on'] ?? null,
                'terms' => $data['terms'] ?? null,
                'status' => 'draft',
                'created_by' => session('admin_id'),
            ]);
            if (! empty($data['vendor_public_id'])) {
                $this->finance->linkVendor($contract, $data['vendor_public_id']);
            }
            if (! empty($data['bill_public_id'])) {
                $this->finance->linkBill($contract, $data['bill_public_id']);
            }
            $this->audit->record('projects', 'contract.created', $contract, after: [
                'project' => $project->public_id,
                'number' => $contract->number,
                'value' => $contract->value,
                'currency' => $contract->currency,
            ]);

            return $contract;
        });
    }

    public function transition(ProjectContract $contract, string $status): ProjectContract
    {
        $allowed = [
            'draft' => ['pending_approval', 'terminated'],
            'pending_approval' => ['approved', 'draft', 'terminated'],
            'approved' => ['signed', 'terminated'],
            'signed' => ['active', 'terminated'],
            'active' => ['completed', 'terminated'],
        ];
        if (! in_array($status, $allowed[$contract->status] ?? [], true)) {
            throw ValidationException::withMessages(['status' => 'This contract status transition is not allowed.']);
        }

        $before = $contract->status;
        $contract->forceFill([
            'status' => $status,
            'approved_by' => $status === 'approved' ? session('admin_id') : $contract->approved_by,
            'approved_at' => $status === 'approved' ? now() : $contract->approved_at,
            'signed_at' => $status === 'signed' ? now() : $contract->signed_at,
        ])->save();

        $this->audit->record('projects', 'contract.status-changed', $contract, before: ['status' => $before], after: [
            'status' => $status,
        ]);
        ProjectContractStatusChanged::dispatch(
            $contract->event_id,
            $contract->org_id,
            $contract->project->public_id,
            $contract->public_id,
            $contract->status,
            $contract->value,
            $contract->currency,
        );

        return $contract->fresh();
    }
}
