<?php

namespace App\Projects\Services;

use App\Projects\Models\Project;
use App\Projects\Models\ProjectIssue;
use App\Projects\Models\ProjectRisk;
use App\Projects\Models\ProjectTask;
use App\Shared\Audit\AuditLogger;

class ProjectRiskIssueService
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function createRisk(Project $project, array $data): ProjectRisk
    {
        $risk = ProjectRisk::query()->create([
            ...$data,
            'project_id' => $project->id,
            'score' => (int) $data['probability'] * (int) $data['impact'],
            'status' => $data['status'] ?? 'open',
            'created_by' => session('admin_id'),
        ]);
        $this->audit->record('projects', 'risk.created', $risk, after: [
            'project' => $project->public_id,
            'title' => $risk->title,
            'score' => $risk->score,
        ]);

        return $risk;
    }

    public function createIssue(Project $project, array $data): ProjectIssue
    {
        $task = isset($data['task_id'])
            ? ProjectTask::query()->where('project_id', $project->id)->findOrFail($data['task_id'])
            : null;
        $issue = ProjectIssue::query()->create([
            ...$data,
            'project_id' => $project->id,
            'task_id' => $task?->id,
            'status' => $data['status'] ?? 'open',
            'created_by' => session('admin_id'),
        ]);
        $this->audit->record('projects', 'issue.created', $issue, after: [
            'project' => $project->public_id,
            'task' => $task?->public_id,
            'title' => $issue->title,
            'severity' => $issue->severity,
        ]);

        return $issue;
    }
}
