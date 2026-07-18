<?php

namespace App\Http\Controllers\Admin\Projects;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Projects\StoreProjectTeamMemberRequest;
use App\Http\Requests\Admin\Projects\StoreProjectTeamRequest;
use App\Models\OrganizationAdminUser;
use App\Projects\Models\ProjectTeam;
use App\Projects\Models\ProjectTeamMember;
use App\Projects\Services\ProjectTeamService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProjectTeamController extends Controller
{
    public function index(): View
    {
        $teams = ProjectTeam::query()
            ->with(['members.administrator'])
            ->withCount(['members', 'projects'])
            ->orderBy('name')
            ->get();
        $administrators = $this->administrators();

        return view('admin.projects.teams.index', compact('teams', 'administrators'));
    }

    public function store(StoreProjectTeamRequest $request, ProjectTeamService $teams): RedirectResponse
    {
        $teams->create($request->validated());

        return redirect()
            ->route('admin.projects.teams.index')
            ->with('success', 'Team created.');
    }

    public function addMember(
        StoreProjectTeamMemberRequest $request,
        ProjectTeam $team,
        ProjectTeamService $teams,
    ): RedirectResponse {
        $teams->addMember(
            $team,
            (int) $request->validated('organization_admin_user_id'),
            $request->validated('role'),
        );

        return back()->with('success', 'Team member added.');
    }

    public function removeMember(
        ProjectTeam $team,
        ProjectTeamMember $member,
        ProjectTeamService $teams,
    ): RedirectResponse {
        $teams->removeMember($team, $member);

        return back()->with('success', 'Team member removed.');
    }

    private function administrators(): Collection
    {
        return OrganizationAdminUser::query()
            ->where('organization_id', config('event.org_id'))
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }
}
