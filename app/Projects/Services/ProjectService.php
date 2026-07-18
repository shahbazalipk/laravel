<?php

namespace App\Projects\Services;

use App\Projects\Models\Project;
use App\Projects\Models\ProjectBoard;
use App\Projects\Models\ProjectMember;
use App\Shared\Audit\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProjectService
{
    public function __construct(
        private readonly ProjectNumberService $numbers,
        private readonly AuditLogger $audit,
    ) {}

    public function create(array $data): Project
    {
        return DB::transaction(function () use ($data): Project {
            $key = strtoupper((string) ($data['key'] ?? ''));

            if (! preg_match('/^[A-Z][A-Z0-9]{1,9}$/', $key)) {
                throw ValidationException::withMessages([
                    'key' => 'Use 2–10 uppercase letters or numbers, starting with a letter.',
                ]);
            }

            $actorId = (int) session('admin_id');
            $project = Project::query()->create([
                ...$data,
                'key' => $key,
                'number' => $this->numbers->next('project', 'PRJ'),
                'owner_admin_id' => $data['owner_admin_id'] ?? $actorId,
                'manager_admin_id' => $data['manager_admin_id'] ?? $actorId,
                'created_by' => $actorId,
                'currency' => isset($data['currency']) ? strtoupper((string) $data['currency']) : null,
                'tags' => array_values(array_filter(array_map(
                    'trim',
                    explode(',', (string) ($data['tags'] ?? ''))
                ))),
            ]);

            $board = ProjectBoard::query()->create([
                'project_id' => $project->id,
                'name' => 'Main board',
                'is_default' => true,
                'sort_order' => 0,
            ]);

            foreach ($this->defaultColumns() as $column) {
                $board->columns()->create($column);
            }

            ProjectMember::query()->create([
                'project_id' => $project->id,
                'organization_admin_user_id' => $project->owner_admin_id,
                'role' => 'owner',
                'can_view_financials' => true,
                'is_active' => true,
                'added_by' => $actorId,
            ]);

            $this->audit->record('projects', 'project.created', $project, after: [
                'number' => $project->number,
                'key' => $project->key,
                'name' => $project->name,
                'status' => $project->status,
            ]);

            return $project->load('boards.columns');
        });
    }

    /** @return list<array<string, mixed>> */
    private function defaultColumns(): array
    {
        return [
            ['name' => 'Backlog', 'slug' => 'backlog', 'status' => 'backlog', 'color' => '#64748b', 'sort_order' => 0],
            ['name' => 'To do', 'slug' => 'todo', 'status' => 'todo', 'color' => '#3b82f6', 'sort_order' => 10],
            ['name' => 'In progress', 'slug' => 'in-progress', 'status' => 'in_progress', 'color' => '#f59e0b', 'sort_order' => 20],
            ['name' => 'Review', 'slug' => 'review', 'status' => 'review', 'color' => '#8b5cf6', 'sort_order' => 30],
            ['name' => 'Done', 'slug' => 'done', 'status' => 'done', 'color' => '#10b981', 'sort_order' => 40, 'is_terminal' => true],
        ];
    }
}
