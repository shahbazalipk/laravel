<?php

namespace App\Http\Controllers\Admin\Projects;

use App\Http\Controllers\Controller;
use App\Projects\Models\Project;
use App\Projects\Models\ProjectTask;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $metrics = [
            'active_projects' => Project::query()->where('status', 'active')->count(),
            'open_tasks' => ProjectTask::query()->whereNotIn('status', ['done', 'cancelled'])->count(),
            'overdue_tasks' => ProjectTask::query()
                ->whereNotIn('status', ['done', 'cancelled'])
                ->whereDate('due_date', '<', today())
                ->count(),
            'completed_tasks' => ProjectTask::query()->where('status', 'done')->count(),
        ];

        $projects = Project::query()
            ->withCount([
                'tasks',
                'tasks as completed_tasks_count' => fn ($query) => $query->where('status', 'done'),
            ])
            ->orderByRaw("case when status = 'active' then 0 else 1 end")
            ->orderBy('due_date')
            ->limit(8)
            ->get();

        $upcomingTasks = ProjectTask::query()
            ->with('project')
            ->whereNotIn('status', ['done', 'cancelled'])
            ->whereNotNull('due_date')
            ->orderBy('due_date')
            ->limit(8)
            ->get();

        return view('admin.projects.dashboard', compact('metrics', 'projects', 'upcomingTasks'));
    }
}
