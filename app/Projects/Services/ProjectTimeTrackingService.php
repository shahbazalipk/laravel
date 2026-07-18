<?php

namespace App\Projects\Services;

use App\Projects\Models\ProjectTask;
use App\Projects\Models\ProjectTimeLog;
use App\Shared\Audit\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProjectTimeTrackingService
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function log(ProjectTask $task, array $data): ProjectTimeLog
    {
        return DB::transaction(function () use ($task, $data): ProjectTimeLog {
            $log = ProjectTimeLog::query()->create([
                'task_id' => $task->id,
                'organization_admin_user_id' => $data['organization_admin_user_id'] ?? session('admin_id'),
                'logged_on' => $data['logged_on'],
                'duration_minutes' => $data['duration_minutes'],
                'description' => $data['description'] ?? null,
                'is_billable' => (bool) ($data['is_billable'] ?? false),
                'approval_status' => $data['approval_status'] ?? 'not_required',
                'created_by' => session('admin_id'),
            ]);
            $this->syncTaskTotal($task);
            $this->record($task, $log, 'time.logged');

            return $log;
        });
    }

    public function start(ProjectTask $task): ProjectTimeLog
    {
        $userId = (int) session('admin_id');
        if (ProjectTimeLog::query()
            ->where('organization_admin_user_id', $userId)
            ->whereNotNull('started_at')
            ->whereNull('ended_at')
            ->exists()) {
            throw ValidationException::withMessages(['timer' => 'Stop your active timer before starting another.']);
        }

        return ProjectTimeLog::query()->create([
            'task_id' => $task->id,
            'organization_admin_user_id' => $userId,
            'logged_on' => today(),
            'started_at' => now(),
            'duration_minutes' => 0,
            'created_by' => $userId,
        ]);
    }

    public function stop(ProjectTimeLog $log): ProjectTimeLog
    {
        if ((int) $log->organization_admin_user_id !== (int) session('admin_id') || ! $log->started_at || $log->ended_at) {
            throw ValidationException::withMessages(['timer' => 'This timer cannot be stopped.']);
        }

        return DB::transaction(function () use ($log): ProjectTimeLog {
            $minutes = max(1, $log->started_at->diffInMinutes(now()));
            $log->forceFill(['ended_at' => now(), 'duration_minutes' => $minutes])->save();
            $this->syncTaskTotal($log->task);
            $this->record($log->task, $log, 'timer.stopped');

            return $log->fresh();
        });
    }

    private function syncTaskTotal(ProjectTask $task): void
    {
        $task->forceFill([
            'logged_minutes' => (int) $task->timeLogs()->sum('duration_minutes'),
            'updated_by' => session('admin_id'),
        ])->save();
    }

    private function record(ProjectTask $task, ProjectTimeLog $log, string $action): void
    {
        $this->audit->record('projects', $action, $task, after: [
            'time_log' => $log->public_id,
            'minutes' => $log->duration_minutes,
            'billable' => $log->is_billable,
        ]);
    }
}
