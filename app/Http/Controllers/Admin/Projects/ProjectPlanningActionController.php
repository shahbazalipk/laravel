<?php

namespace App\Http\Controllers\Admin\Projects;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Projects\InstantiateProjectTemplateRequest;
use App\Http\Requests\Admin\Projects\InviteProjectGuestRequest;
use App\Http\Requests\Admin\Projects\StoreProjectDependencyRequest;
use App\Http\Requests\Admin\Projects\StoreProjectRecurrenceRequest;
use App\Http\Requests\Admin\Projects\StoreProjectSavedFilterRequest;
use App\Http\Requests\Admin\Projects\StoreProjectTemplateRequest;
use App\Http\Requests\Admin\Projects\UpdateProjectTaskDatesRequest;
use App\Projects\Models\Project;
use App\Projects\Models\ProjectRecurrenceRule;
use App\Projects\Models\ProjectTask;
use App\Projects\Models\ProjectTemplate;
use App\Projects\Services\ProjectDependencyService;
use App\Projects\Services\ProjectGuestService;
use App\Projects\Services\ProjectRecurrenceService;
use App\Projects\Services\ProjectSavedFilterService;
use App\Projects\Services\ProjectTemplateService;
use Illuminate\Http\RedirectResponse;

class ProjectPlanningActionController extends Controller
{
    public function dependency(
        StoreProjectDependencyRequest $request,
        Project $project,
        ProjectTask $task,
        ProjectDependencyService $service,
    ): RedirectResponse {
        $this->assertTaskProject($project, $task);
        $dependsOn = ProjectTask::query()
            ->where('project_id', $project->id)
            ->findOrFail($request->integer('depends_on_task_id'));
        $service->add($task, $dependsOn, $request->validated());

        return back()->with('success', 'Task dependency added.');
    }

    public function recurrence(
        StoreProjectRecurrenceRequest $request,
        Project $project,
        ProjectTask $task,
        ProjectRecurrenceService $service,
    ): RedirectResponse {
        $this->assertTaskProject($project, $task);
        $service->create($task, $request->validated());

        return back()->with('success', 'Recurrence schedule created.');
    }

    public function generate(ProjectRecurrenceRule $rule, ProjectRecurrenceService $service): RedirectResponse
    {
        $generated = $service->generateDue($rule);

        return back()->with('success', count($generated).' recurring task(s) generated.');
    }

    public function captureTemplate(
        StoreProjectTemplateRequest $request,
        Project $project,
        ProjectTemplateService $service,
    ): RedirectResponse {
        $service->capture($project, $request->validated());

        return back()->with('success', 'Project template created.');
    }

    public function instantiateTemplate(
        InstantiateProjectTemplateRequest $request,
        ProjectTemplate $template,
        ProjectTemplateService $service,
    ): RedirectResponse {
        $project = $service->instantiate($template, $request->validated());

        return redirect()->route('admin.projects.show', $project)->with('success', 'Project created from template.');
    }

    public function savedFilter(
        StoreProjectSavedFilterRequest $request,
        ProjectSavedFilterService $service,
    ): RedirectResponse {
        $service->save($request->validated());

        return back()->with('success', 'Filter saved.');
    }

    public function inviteGuest(
        InviteProjectGuestRequest $request,
        Project $project,
        ProjectGuestService $service,
    ): RedirectResponse {
        $result = $service->invite($project, $request->validated());

        return back()
            ->with('success', 'Guest invitation created.')
            ->with('guest_invitation_token', $result['token']);
    }

    public function updateDates(
        UpdateProjectTaskDatesRequest $request,
        Project $project,
        ProjectTask $task,
    ): RedirectResponse {
        $this->assertTaskProject($project, $task);
        $task->forceFill([
            ...$request->validated(),
            'updated_by' => session('admin_id'),
            'version' => $task->version + 1,
        ])->save();

        return back()->with('success', 'Task dates updated.');
    }

    private function assertTaskProject(Project $project, ProjectTask $task): void
    {
        abort_unless((int) $task->project_id === (int) $project->id, 404);
    }
}
