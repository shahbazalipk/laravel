<?php

namespace App\Projects\Services;

use App\Projects\Models\ProjectTask;
use App\Projects\Models\ProjectTaskApprovalRequest;
use App\Projects\Models\ProjectTaskApprovalStep;
use App\Shared\Audit\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProjectTaskApprovalService
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function request(ProjectTask $task, array $data): ProjectTaskApprovalRequest
    {
        if ($task->approvalRequests()->where('status', 'pending')->exists()) {
            throw ValidationException::withMessages(['approval' => 'This task already has a pending approval.']);
        }

        return DB::transaction(function () use ($task, $data): ProjectTaskApprovalRequest {
            $request = ProjectTaskApprovalRequest::query()->create([
                'task_id' => $task->id,
                'mode' => $data['mode'],
                'status' => 'pending',
                'instructions' => $data['instructions'] ?? null,
                'requested_by' => session('admin_id'),
                'requested_at' => now(),
                'current_sequence' => 1,
            ]);

            foreach (array_values(array_unique($data['approver_admin_ids'])) as $index => $approverId) {
                ProjectTaskApprovalStep::query()->create([
                    'approval_request_id' => $request->id,
                    'approver_admin_id' => $approverId,
                    'sequence' => $data['mode'] === 'sequential' ? $index + 1 : 1,
                    'status' => 'pending',
                ]);
            }

            $this->audit->record('projects', 'task.approval-requested', $task, after: [
                'approval' => $request->public_id,
                'mode' => $request->mode,
                'approvers' => count($data['approver_admin_ids']),
            ]);

            return $request->load('steps.approver');
        });
    }

    public function decide(ProjectTaskApprovalStep $step, string $decision, ?string $comments = null): ProjectTaskApprovalRequest
    {
        if ((int) $step->approver_admin_id !== (int) session('admin_id') || $step->status !== 'pending') {
            throw ValidationException::withMessages(['decision' => 'This approval step is not available to you.']);
        }

        return DB::transaction(function () use ($step, $decision, $comments): ProjectTaskApprovalRequest {
            $request = ProjectTaskApprovalRequest::query()->lockForUpdate()->findOrFail($step->approval_request_id);
            if ($request->status !== 'pending'
                || ($request->mode === 'sequential' && $step->sequence !== $request->current_sequence)) {
                throw ValidationException::withMessages(['decision' => 'This approval step is not currently active.']);
            }

            $step->forceFill([
                'status' => $decision,
                'comments' => $comments,
                'decided_at' => now(),
            ])->save();

            if (in_array($decision, ['rejected', 'changes_requested'], true)) {
                $request->status = $decision;
            } elseif ($request->mode === 'any') {
                $request->status = 'approved';
                $request->steps()->where('status', 'pending')->update(['status' => 'skipped', 'updated_at' => now()]);
            } elseif (! $request->steps()->where('status', 'pending')->exists()) {
                $request->status = 'approved';
            } elseif ($request->mode === 'sequential') {
                $request->current_sequence = (int) $request->steps()->where('status', 'pending')->min('sequence');
            }

            if ($request->status !== 'pending') {
                $request->completed_at = now();
            }
            $request->save();

            $this->audit->record('projects', 'task.approval-decided', $request->task, after: [
                'approval' => $request->public_id,
                'decision' => $decision,
                'status' => $request->status,
            ]);

            return $request->fresh(['steps.approver', 'task']);
        });
    }
}
