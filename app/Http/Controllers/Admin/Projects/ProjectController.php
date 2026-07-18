<?php

namespace App\Http\Controllers\Admin\Projects;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Projects\StoreProjectRequest;
use App\Models\OrganizationAdminUser;
use App\Projects\Models\Project;
use App\Projects\Services\ProjectService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $projects = Project::query()
            ->withCount(['members', 'tasks'])
            ->when(
                $request->filled('status'),
                fn ($query) => $query->where('status', $request->string('status')->toString())
            )
            ->when(
                $request->filled('search'),
                fn ($query) => $query->where(function ($nested) use ($request): void {
                    $search = '%'.$request->string('search')->toString().'%';
                    $nested->where('name', 'like', $search)
                        ->orWhere('key', 'like', $search)
                        ->orWhere('number', 'like', $search);
                })
            )
            ->orderByRaw("case when status = 'active' then 0 else 1 end")
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.projects.projects.index', compact('projects'));
    }

    public function create(): View
    {
        return view('admin.projects.projects.create');
    }

    public function store(StoreProjectRequest $request, ProjectService $projects): RedirectResponse
    {
        $project = $projects->create($request->validated());

        return redirect()
            ->route('admin.projects.show', $project)
            ->with('success', 'Project created with a default workflow.');
    }

    public function show(Project $project): View
    {
        $project->load([
            'members.administrator',
            'boards' => fn ($query) => $query->orderByDesc('is_default')->orderBy('sort_order'),
            'boards.columns.tasks' => fn ($query) => $query->with('project')->orderBy('position'),
        ])->loadCount(['members', 'tasks']);

        $board = $project->boards->first();
        $administrators = OrganizationAdminUser::query()
            ->where('organization_id', config('event.org_id'))
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('admin.projects.projects.show', compact('project', 'board', 'administrators'));
    }
}
