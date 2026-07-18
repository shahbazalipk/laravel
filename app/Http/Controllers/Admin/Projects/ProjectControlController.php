<?php

namespace App\Http\Controllers\Admin\Projects;

use App\Http\Controllers\Controller;
use App\Models\OrganizationAdminUser;
use App\Projects\Models\Project;
use App\Projects\Models\ProjectIssue;
use App\Projects\Models\ProjectRisk;
use App\Projects\Models\ProjectTaskApprovalStep;
use App\Projects\Models\ProjectTimeLog;
use App\Projects\Services\ProjectWorkloadService;
use Illuminate\View\View;

class ProjectControlController extends Controller
{
    public function __invoke(ProjectWorkloadService $workload): View
    {
        $weeklyWorkload = $workload->weekly();
        $timeLogs = ProjectTimeLog::query()->with(['task.project', 'user'])->latest('logged_on')->limit(30)->get();
        $approvalSteps = ProjectTaskApprovalStep::query()
            ->with(['request.task.project', 'approver'])
            ->where('approver_admin_id', session('admin_id'))
            ->where('status', 'pending')
            ->latest()
            ->get();
        $risks = ProjectRisk::query()->with('project')->orderByDesc('score')->latest()->get();
        $issues = ProjectIssue::query()->with(['project', 'task'])->orderByRaw(
            "CASE severity WHEN 'critical' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 ELSE 4 END"
        )->latest()->get();
        $projects = Project::query()->with('tasks')->orderBy('name')->get();
        $administrators = OrganizationAdminUser::query()
            ->where('organization_id', config('event.org_id'))
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('admin.projects.controls.index', compact(
            'weeklyWorkload',
            'timeLogs',
            'approvalSteps',
            'risks',
            'issues',
            'projects',
            'administrators',
        ));
    }
}
