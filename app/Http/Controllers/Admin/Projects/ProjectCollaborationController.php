<?php

namespace App\Http\Controllers\Admin\Projects;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Projects\StoreProjectChecklistItemRequest;
use App\Http\Requests\Admin\Projects\StoreProjectCommentRequest;
use App\Http\Requests\Admin\Projects\ToggleProjectChecklistItemRequest;
use App\Projects\Models\Project;
use App\Projects\Models\ProjectTask;
use App\Projects\Models\ProjectTaskChecklistItem;
use App\Projects\Services\ProjectCollaborationService;
use Illuminate\Http\RedirectResponse;

class ProjectCollaborationController extends Controller
{
    public function comment(
        StoreProjectCommentRequest $request,
        Project $project,
        ProjectTask $task,
        ProjectCollaborationService $collaboration,
    ): RedirectResponse {
        $this->assertTaskProject($task, $project);
        $collaboration->addComment($task, $request->validated());

        return back()->with('success', 'Comment added.');
    }

    public function checklist(
        StoreProjectChecklistItemRequest $request,
        Project $project,
        ProjectTask $task,
        ProjectCollaborationService $collaboration,
    ): RedirectResponse {
        $this->assertTaskProject($task, $project);
        $collaboration->addChecklistItem($task, $request->validated());

        return back()->with('success', 'Checklist item added.');
    }

    public function toggleChecklist(
        ToggleProjectChecklistItemRequest $request,
        Project $project,
        ProjectTask $task,
        ProjectTaskChecklistItem $item,
        ProjectCollaborationService $collaboration,
    ): RedirectResponse {
        $this->assertTaskProject($task, $project);
        $collaboration->toggleChecklistItem($task, $item, $request->boolean('is_completed'));

        return back()->with('success', 'Checklist updated.');
    }

    private function assertTaskProject(ProjectTask $task, Project $project): void
    {
        abort_unless((int) $task->project_id === (int) $project->id, 404);
    }
}
