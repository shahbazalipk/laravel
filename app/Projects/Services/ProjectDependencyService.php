<?php

namespace App\Projects\Services;

use App\Projects\Models\ProjectTask;
use App\Projects\Models\ProjectTaskDependency;
use App\Shared\Audit\AuditLogger;
use Illuminate\Validation\ValidationException;

class ProjectDependencyService
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function add(ProjectTask $task, ProjectTask $dependsOn, array $data): ProjectTaskDependency
    {
        if ((int) $task->project_id !== (int) $dependsOn->project_id) {
            throw ValidationException::withMessages(['depends_on_task_id' => 'Tasks must belong to the same project.']);
        }
        if ($task->is($dependsOn) || $this->reaches($dependsOn, $task->id)) {
            throw ValidationException::withMessages(['depends_on_task_id' => 'This dependency would create a cycle.']);
        }

        $dependency = ProjectTaskDependency::query()->create([
            'task_id' => $task->id,
            'depends_on_task_id' => $dependsOn->id,
            'type' => $data['type'],
            'lag_days' => (int) ($data['lag_days'] ?? 0),
            'is_enforced' => (bool) ($data['is_enforced'] ?? true),
            'created_by' => session('admin_id'),
        ]);

        $this->audit->record('projects', 'task.dependency-added', $task, after: [
            'depends_on' => $dependsOn->public_id,
            'type' => $dependency->type,
            'lag_days' => $dependency->lag_days,
        ]);

        return $dependency;
    }

    public function assertCanComplete(ProjectTask $task): void
    {
        $approval = $task->approvalRequests()->latest('requested_at')->first();
        if ($approval && $approval->status !== 'approved') {
            throw ValidationException::withMessages([
                'column_id' => 'Complete the task approval before marking this task done.',
            ]);
        }

        $blocking = $task->dependencies()
            ->where('is_enforced', true)
            ->whereIn('type', ['blocks', 'is_blocked_by', 'starts_after'])
            ->whereHas('dependsOn', fn ($query) => $query->whereNotIn('status', ['done', 'cancelled']))
            ->with('dependsOn')
            ->first();

        if ($blocking) {
            throw ValidationException::withMessages([
                'column_id' => "Complete dependency {$blocking->dependsOn->key} first.",
            ]);
        }

        if ($task->checklistItems()->where('is_required', true)->where('is_completed', false)->exists()) {
            throw ValidationException::withMessages([
                'column_id' => 'Complete all required checklist items first.',
            ]);
        }
    }

    private function reaches(ProjectTask $from, int $targetId, array $visited = []): bool
    {
        if (in_array($from->id, $visited, true)) {
            return false;
        }
        $visited[] = $from->id;

        foreach ($from->dependencies()->with('dependsOn')->get() as $dependency) {
            if ((int) $dependency->depends_on_task_id === $targetId
                || $this->reaches($dependency->dependsOn, $targetId, $visited)) {
                return true;
            }
        }

        return false;
    }
}
