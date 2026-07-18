<?php

namespace App\Http\Controllers\Admin\Projects;

use App\Http\Controllers\Controller;
use App\Projects\Models\Project;
use App\Projects\Models\ProjectGuest;
use App\Projects\Models\ProjectSavedFilter;
use App\Projects\Models\ProjectTask;
use App\Projects\Models\ProjectTemplate;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectPlanningController extends Controller
{
    public function calendar(Request $request): View
    {
        $month = CarbonImmutable::parse($request->string('month')->value() ?: 'today')->startOfMonth();
        $tasks = ProjectTask::query()
            ->with('project')
            ->where(function ($query) use ($month): void {
                $query->whereBetween('start_date', [$month, $month->endOfMonth()])
                    ->orWhereBetween('due_date', [$month, $month->endOfMonth()]);
            })
            ->orderBy('due_date')
            ->get();
        $filters = $this->filters('calendar');

        return view('admin.projects.planning.calendar', compact('month', 'tasks', 'filters'));
    }

    public function timeline(): View
    {
        $projects = Project::query()
            ->with(['tasks' => fn ($query) => $query->with('dependencies.dependsOn')->orderBy('start_date')])
            ->whereNotIn('status', ['archived', 'cancelled'])
            ->orderBy('start_date')
            ->get();
        $filters = $this->filters('timeline');

        return view('admin.projects.planning.timeline', compact('projects', 'filters'));
    }

    public function templates(): View
    {
        $templates = ProjectTemplate::query()->where('is_active', true)->latest()->get();
        $projects = Project::query()->whereNotIn('status', ['archived', 'cancelled'])->orderBy('name')->get();

        return view('admin.projects.planning.templates', compact('templates', 'projects'));
    }

    public function guests(): View
    {
        $guests = ProjectGuest::query()->with('accessGrants.project')->latest()->get();
        $projects = Project::query()->with('tasks')->orderBy('name')->get();

        return view('admin.projects.planning.guests', compact('guests', 'projects'));
    }

    private function filters(string $view)
    {
        return ProjectSavedFilter::query()
            ->where('view', $view)
            ->where(function ($query): void {
                $query->where('organization_admin_user_id', session('admin_id'))
                    ->orWhere('visibility', 'shared');
            })
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();
    }
}
