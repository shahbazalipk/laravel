<?php

namespace App\Http\Controllers\Admin\Projects;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Projects\StoreProjectMemberRequest;
use App\Projects\Models\Project;
use App\Projects\Models\ProjectMember;
use App\Projects\Services\ProjectMembershipService;
use Illuminate\Http\RedirectResponse;

class ProjectMemberController extends Controller
{
    public function store(
        StoreProjectMemberRequest $request,
        Project $project,
        ProjectMembershipService $memberships,
    ): RedirectResponse {
        $memberships->add(
            $project,
            (int) $request->validated('organization_admin_user_id'),
            $request->validated('role'),
            $request->boolean('can_view_financials'),
        );

        return back()->with('success', 'Project member added.');
    }

    public function destroy(
        Project $project,
        ProjectMember $member,
        ProjectMembershipService $memberships,
    ): RedirectResponse {
        $memberships->remove($project, $member);

        return back()->with('success', 'Project member removed.');
    }
}
