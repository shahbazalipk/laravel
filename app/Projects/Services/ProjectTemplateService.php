<?php

namespace App\Projects\Services;

use App\Projects\Models\Project;
use App\Projects\Models\ProjectTask;
use App\Projects\Models\ProjectTemplate;
use App\Shared\Audit\AuditLogger;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class ProjectTemplateService
{
    public function __construct(
        private readonly ProjectService $projects,
        private readonly ProjectTaskService $tasks,
        private readonly ProjectDependencyService $dependencies,
        private readonly ProjectCollaborationService $collaboration,
        private readonly AuditLogger $audit,
    ) {}

    public function capture(Project $project, array $data): ProjectTemplate
    {
        $project->load(['tasks.dependencies.dependsOn', 'tasks.checklistItems']);
        $anchor = $project->start_date
            ?? $project->tasks->pluck('start_date')->filter()->sort()->first()
            ?? today();

        $definitionTasks = $project->tasks->whereNull('parent_task_id')->map(function (ProjectTask $task) use ($anchor): array {
            return [
                'source_key' => $task->key,
                'title' => $task->title,
                'description' => $task->description,
                'type' => $task->type,
                'priority' => $task->priority,
                'start_offset_days' => $task->start_date ? $anchor->diffInDays($task->start_date, false) : null,
                'due_offset_days' => $task->due_date ? $anchor->diffInDays($task->due_date, false) : null,
                'estimated_minutes' => $task->estimated_minutes,
                'labels' => $task->labels,
                'location' => $task->location,
                'checklist' => $task->checklistItems->map(fn ($item) => [
                    'text' => $item->text,
                    'is_required' => $item->is_required,
                ])->values()->all(),
                'dependencies' => $task->dependencies->map(fn ($dependency) => [
                    'depends_on_source_key' => $dependency->dependsOn->key,
                    'type' => $dependency->type,
                    'lag_days' => $dependency->lag_days,
                    'is_enforced' => $dependency->is_enforced,
                ])->values()->all(),
            ];
        })->values()->all();

        $template = ProjectTemplate::query()->create([
            'name' => $data['name'],
            'description' => $data['description'] ?? $project->description,
            'category' => $data['category'] ?? $project->type,
            'definition' => [
                'project' => [
                    'description' => $project->description,
                    'type' => $project->type,
                    'priority' => $project->priority,
                    'tags' => $project->tags,
                    'color' => $project->color,
                ],
                'tasks' => $definitionTasks,
            ],
            'task_count' => count($definitionTasks),
            'is_active' => true,
            'is_shared' => (bool) ($data['is_shared'] ?? false),
            'created_by' => session('admin_id'),
        ]);

        $this->audit->record('projects', 'template.created', $template, after: [
            'name' => $template->name,
            'source_project' => $project->public_id,
            'task_count' => $template->task_count,
        ]);

        return $template;
    }

    public function instantiate(ProjectTemplate $template, array $data): Project
    {
        return DB::transaction(function () use ($template, $data): Project {
            $start = CarbonImmutable::parse($data['start_date']);
            $definition = $template->definition;
            $projectDefaults = $definition['project'] ?? [];
            $project = $this->projects->create([
                ...$projectDefaults,
                'name' => $data['name'],
                'key' => $data['key'],
                'start_date' => $start->toDateString(),
                'due_date' => $data['due_date'] ?? null,
                'type' => $projectDefaults['type'] ?? 'event_operations',
                'priority' => $projectDefaults['priority'] ?? 'medium',
                'status' => 'planned',
                'visibility' => $data['visibility'] ?? 'members',
                'tags' => implode(',', $projectDefaults['tags'] ?? []),
            ]);

            $created = [];
            foreach ($definition['tasks'] ?? [] as $taskDefinition) {
                $task = $this->tasks->create($project, [
                    'title' => $taskDefinition['title'],
                    'description' => $taskDefinition['description'] ?? null,
                    'type' => $taskDefinition['type'] ?? 'task',
                    'priority' => $taskDefinition['priority'] ?? 'medium',
                    'start_date' => isset($taskDefinition['start_offset_days'])
                        ? $start->addDays($taskDefinition['start_offset_days'])->toDateString()
                        : null,
                    'due_date' => isset($taskDefinition['due_offset_days'])
                        ? $start->addDays($taskDefinition['due_offset_days'])->toDateString()
                        : null,
                    'estimated_minutes' => $taskDefinition['estimated_minutes'] ?? null,
                    'labels' => implode(',', $taskDefinition['labels'] ?? []),
                    'location' => $taskDefinition['location'] ?? null,
                ]);
                foreach ($taskDefinition['checklist'] ?? [] as $item) {
                    $this->collaboration->addChecklistItem($task, $item);
                }
                $created[$taskDefinition['source_key']] = $task;
            }

            foreach ($definition['tasks'] ?? [] as $taskDefinition) {
                foreach ($taskDefinition['dependencies'] ?? [] as $dependency) {
                    if (isset($created[$taskDefinition['source_key']], $created[$dependency['depends_on_source_key']])) {
                        $this->dependencies->add(
                            $created[$taskDefinition['source_key']],
                            $created[$dependency['depends_on_source_key']],
                            $dependency,
                        );
                    }
                }
            }

            return $project->fresh(['boards.columns', 'tasks']);
        });
    }
}
