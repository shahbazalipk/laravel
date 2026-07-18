<?php

namespace App\Projects\Services;

use App\Projects\Models\Project;
use App\Projects\Models\ProjectBoard;
use App\Projects\Models\ProjectBoardColumn;
use App\Projects\Models\ProjectTask;
use App\Shared\Audit\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProjectTaskService
{
    public function __construct(
        private readonly ProjectNumberService $numbers,
        private readonly ProjectDependencyService $dependencies,
        private readonly AuditLogger $audit,
    ) {}

    public function create(Project $project, array $data): ProjectTask
    {
        return DB::transaction(function () use ($project, $data): ProjectTask {
            $board = $this->boardForProject($project, $data['board_id'] ?? null);
            $column = $this->columnForBoard($board, $data['column_id'] ?? null);
            $sequence = $this->numbers->next("task:{$project->id}", $project->key);

            $task = ProjectTask::query()->create([
                ...$data,
                'project_id' => $project->id,
                'board_id' => $board->id,
                'column_id' => $column->id,
                'number' => $sequence,
                'key' => $sequence,
                'status' => $column->status,
                'reporter_admin_id' => (int) session('admin_id'),
                'created_by' => (int) session('admin_id'),
                'updated_by' => (int) session('admin_id'),
                'position' => ((int) $column->tasks()->max('position')) + 1000,
                'labels' => array_values(array_filter(array_map(
                    'trim',
                    explode(',', (string) ($data['labels'] ?? ''))
                ))),
            ]);

            DB::table('project_task_status_histories')->insert([
                'public_id' => (string) \Illuminate\Support\Str::uuid(),
                'event_id' => $task->event_id,
                'org_id' => $task->org_id,
                'task_id' => $task->id,
                'to_column_id' => $column->id,
                'actor_admin_id' => session('admin_id'),
                'metadata' => json_encode(['source' => 'task.created'], JSON_THROW_ON_ERROR),
                'created_at' => now(),
            ]);

            $this->audit->record('projects', 'task.created', $task, after: [
                'project' => $project->public_id,
                'key' => $task->key,
                'title' => $task->title,
                'status' => $task->status,
            ]);

            return $task;
        });
    }

    public function move(ProjectTask $task, ProjectBoardColumn $column): ProjectTask
    {
        if ((int) $task->board_id !== (int) $column->board_id) {
            throw ValidationException::withMessages([
                'column_id' => 'The selected column does not belong to this task board.',
            ]);
        }

        if ($column->work_in_progress_limit !== null
            && $column->tasks()->whereKeyNot($task->id)->count() >= $column->work_in_progress_limit) {
            throw ValidationException::withMessages([
                'column_id' => 'This column has reached its work-in-progress limit.',
            ]);
        }
        if ($column->is_terminal) {
            $this->dependencies->assertCanComplete($task);
        }

        return DB::transaction(function () use ($task, $column): ProjectTask {
            $fromColumnId = $task->column_id;
            $before = ['column_id' => $fromColumnId, 'status' => $task->status];

            $task->forceFill([
                'column_id' => $column->id,
                'status' => $column->status,
                'position' => ((int) $column->tasks()->max('position')) + 1000,
                'progress_percent' => $column->is_terminal ? 100 : $task->progress_percent,
                'completed_at' => $column->is_terminal ? now() : null,
                'updated_by' => session('admin_id'),
                'version' => $task->version + 1,
            ])->save();

            DB::table('project_task_status_histories')->insert([
                'public_id' => (string) \Illuminate\Support\Str::uuid(),
                'event_id' => $task->event_id,
                'org_id' => $task->org_id,
                'task_id' => $task->id,
                'from_column_id' => $fromColumnId,
                'to_column_id' => $column->id,
                'actor_admin_id' => session('admin_id'),
                'metadata' => json_encode(['source' => 'board.move'], JSON_THROW_ON_ERROR),
                'created_at' => now(),
            ]);

            $this->audit->record('projects', 'task.moved', $task, before: $before, after: [
                'column_id' => $column->id,
                'status' => $column->status,
            ]);

            return $task->fresh();
        });
    }

    private function boardForProject(Project $project, mixed $boardId): ProjectBoard
    {
        return $project->boards()
            ->when($boardId, fn ($query) => $query->where('public_id', $boardId))
            ->orderByDesc('is_default')
            ->firstOrFail();
    }

    private function columnForBoard(ProjectBoard $board, mixed $columnId): ProjectBoardColumn
    {
        return $board->columns()
            ->when($columnId, fn ($query) => $query->where('public_id', $columnId))
            ->orderBy('sort_order')
            ->firstOrFail();
    }
}
