<?php

namespace App\Http\Controllers\Admin\Projects;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Projects\MoveProjectTaskRequest;
use App\Http\Requests\Admin\Projects\StoreProjectTaskRequest;
use App\Models\OrganizationAdminUser;
use App\Projects\Models\Project;
use App\Projects\Models\ProjectBoardColumn;
use App\Projects\Models\ProjectTask;
use App\Projects\Services\ProjectTaskService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProjectTaskController extends Controller
{
    public function show(Project $project, ProjectTask $task): View
    {
        abort_unless((int) $task->project_id === (int) $project->id, 404);

        $task->load([
            'column',
            'board.columns',
            'checklistItems',
            'comments.author',
            'dependencies.dependsOn',
            'recurrenceRules',
            'timeLogs.user',
            'approvalRequests.steps.approver',
        ]);
        $availableDependencies = $project->tasks()
            ->whereKeyNot($task->id)
            ->orderBy('key')
            ->get(['id', 'key', 'title']);
        $administrators = OrganizationAdminUser::query()
            ->where('organization_id', config('event.org_id'))
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('admin.projects.tasks.show', compact(
            'project',
            'task',
            'availableDependencies',
            'administrators',
        ));
    }

    public function store(
        StoreProjectTaskRequest $request,
        Project $project,
        ProjectTaskService $tasks,
    ): RedirectResponse {
        $tasks->create($project, $request->validated());

        return redirect()
            ->route('admin.projects.show', $project)
            ->with('success', 'Task created.');
    }

    public function move(
        MoveProjectTaskRequest $request,
        Project $project,
        ProjectTask $task,
        ProjectTaskService $tasks,
    ): RedirectResponse {
        abort_unless((int) $task->project_id === (int) $project->id, 404);

        $column = ProjectBoardColumn::query()
            ->where('public_id', $request->validated('column_id'))
            ->whereHas('board', fn ($query) => $query->where('project_id', $project->id))
            ->firstOrFail();

        $tasks->move($task, $column);

        return redirect()
            ->route('admin.projects.show', $project)
            ->with('success', 'Task moved.');
    }
}
