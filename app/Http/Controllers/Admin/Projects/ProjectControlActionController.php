<?php

namespace App\Http\Controllers\Admin\Projects;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Projects\DecideProjectTaskApprovalRequest;
use App\Http\Requests\Admin\Projects\StoreProjectIssueRequest;
use App\Http\Requests\Admin\Projects\StoreProjectRiskRequest;
use App\Http\Requests\Admin\Projects\StoreProjectTaskApprovalRequest;
use App\Http\Requests\Admin\Projects\StoreProjectTimeLogRequest;
use App\Projects\Models\Project;
use App\Projects\Models\ProjectTask;
use App\Projects\Models\ProjectTaskApprovalStep;
use App\Projects\Models\ProjectTimeLog;
use App\Projects\Services\ProjectRiskIssueService;
use App\Projects\Services\ProjectTaskApprovalService;
use App\Projects\Services\ProjectTimeTrackingService;
use Illuminate\Http\RedirectResponse;

class ProjectControlActionController extends Controller
{
    public function logTime(
        StoreProjectTimeLogRequest $request,
        Project $project,
        ProjectTask $task,
        ProjectTimeTrackingService $service,
    ): RedirectResponse {
        $this->assertTaskProject($project, $task);
        $service->log($task, $request->validated());

        return back()->with('success', 'Time entry recorded.');
    }

    public function startTimer(
        Project $project,
        ProjectTask $task,
        ProjectTimeTrackingService $service,
    ): RedirectResponse {
        $this->assertTaskProject($project, $task);
        $service->start($task);

        return back()->with('success', 'Timer started.');
    }

    public function stopTimer(ProjectTimeLog $timeLog, ProjectTimeTrackingService $service): RedirectResponse
    {
        $service->stop($timeLog);

        return back()->with('success', 'Timer stopped.');
    }

    public function requestApproval(
        StoreProjectTaskApprovalRequest $request,
        Project $project,
        ProjectTask $task,
        ProjectTaskApprovalService $service,
    ): RedirectResponse {
        $this->assertTaskProject($project, $task);
        $service->request($task, $request->validated());

        return back()->with('success', 'Task approval requested.');
    }

    public function decideApproval(
        DecideProjectTaskApprovalRequest $request,
        ProjectTaskApprovalStep $step,
        ProjectTaskApprovalService $service,
    ): RedirectResponse {
        $service->decide($step, $request->string('decision')->value(), $request->string('comments')->value() ?: null);

        return back()->with('success', 'Approval decision recorded.');
    }

    public function risk(
        StoreProjectRiskRequest $request,
        Project $project,
        ProjectRiskIssueService $service,
    ): RedirectResponse {
        $service->createRisk($project, $request->validated());

        return back()->with('success', 'Risk added to the register.');
    }

    public function issue(
        StoreProjectIssueRequest $request,
        Project $project,
        ProjectRiskIssueService $service,
    ): RedirectResponse {
        $service->createIssue($project, $request->validated());

        return back()->with('success', 'Issue added to the register.');
    }

    private function assertTaskProject(Project $project, ProjectTask $task): void
    {
        abort_unless((int) $task->project_id === (int) $project->id, 404);
    }
}
