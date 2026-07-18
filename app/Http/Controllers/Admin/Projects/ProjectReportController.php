<?php

namespace App\Http\Controllers\Admin\Projects;

use App\Http\Controllers\Controller;
use App\Projects\Models\Project;
use App\Projects\Models\ProjectIssue;
use App\Projects\Models\ProjectRisk;
use App\Projects\Models\ProjectTask;
use App\Projects\Models\ProjectTimeLog;
use App\Projects\Services\ProjectWorkloadService;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProjectReportController extends Controller
{
    public function __invoke(ProjectWorkloadService $workload): View
    {
        $statusBreakdown = ProjectTask::query()
            ->select('status', DB::raw('count(*) as aggregate'))
            ->groupBy('status')
            ->orderByDesc('aggregate')
            ->pluck('aggregate', 'status');

        $priorityBreakdown = ProjectTask::query()
            ->whereNotIn('status', ['done', 'cancelled'])
            ->select('priority', DB::raw('count(*) as aggregate'))
            ->groupBy('priority')
            ->pluck('aggregate', 'priority');

        $projectPerformance = Project::query()
            ->withCount([
                'tasks',
                'tasks as completed_tasks_count' => fn ($query) => $query->where('status', 'done'),
                'tasks as overdue_tasks_count' => fn ($query) => $query
                    ->whereNotIn('status', ['done', 'cancelled'])
                    ->whereDate('due_date', '<', today()),
                'risks as open_risks_count' => fn ($query) => $query->whereNotIn('status', ['closed']),
                'issues as open_issues_count' => fn ($query) => $query->whereNotIn('status', ['resolved', 'closed']),
            ])
            ->withSum('tasks', 'estimated_minutes')
            ->withSum('tasks', 'logged_minutes')
            ->orderByDesc('tasks_count')
            ->limit(20)
            ->get();

        $totalTasks = (int) $statusBreakdown->sum();
        $completedTasks = (int) ($statusBreakdown['done'] ?? 0);
        $summary = [
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'completion_rate' => $totalTasks > 0 ? (int) round(($completedTasks / $totalTasks) * 100) : 0,
            'overdue_tasks' => ProjectTask::query()
                ->whereNotIn('status', ['done', 'cancelled'])
                ->whereDate('due_date', '<', today())
                ->count(),
            'estimated_hours' => round(((int) ProjectTask::query()->sum('estimated_minutes')) / 60, 1),
            'logged_hours' => round(((int) ProjectTimeLog::query()->sum('duration_minutes')) / 60, 1),
            'open_risks' => ProjectRisk::query()->whereNotIn('status', ['closed'])->count(),
            'open_issues' => ProjectIssue::query()->whereNotIn('status', ['resolved', 'closed'])->count(),
        ];
        $taskAging = ProjectTask::query()
            ->whereNotIn('status', ['done', 'cancelled'])
            ->get(['created_at'])
            ->groupBy(fn ($task) => match (true) {
                $task->created_at->diffInDays(now()) <= 7 => '0–7 days',
                $task->created_at->diffInDays(now()) <= 30 => '8–30 days',
                $task->created_at->diffInDays(now()) <= 60 => '31–60 days',
                default => '60+ days',
            })
            ->map->count();
        $workloadSummary = $workload->weekly()
            ->groupBy('indicator')
            ->map->count();

        return view('admin.projects.reports.index', compact(
            'statusBreakdown',
            'priorityBreakdown',
            'projectPerformance',
            'summary',
            'taskAging',
            'workloadSummary',
        ));
    }
}
