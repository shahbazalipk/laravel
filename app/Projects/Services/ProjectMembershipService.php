<?php

namespace App\Projects\Services;

use App\Models\OrganizationAdminUser;
use App\Projects\Models\Project;
use App\Projects\Models\ProjectMember;
use App\Shared\Audit\AuditLogger;
use App\Shared\Tenancy\TenantContext;
use Illuminate\Validation\ValidationException;

class ProjectMembershipService
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function add(Project $project, int $administratorId, string $role, bool $canViewFinancials): ProjectMember
    {
        $administrator = OrganizationAdminUser::query()->findOrFail($administratorId);

        if ((int) $administrator->organization_id !== TenantContext::fromConfig()->organizationId) {
            throw ValidationException::withMessages([
                'organization_admin_user_id' => 'The selected administrator is outside this organization.',
            ]);
        }

        $member = ProjectMember::query()->updateOrCreate(
            [
                'project_id' => $project->id,
                'organization_admin_user_id' => $administrator->id,
            ],
            [
                'role' => $role,
                'can_view_financials' => $canViewFinancials,
                'is_active' => true,
                'added_by' => session('admin_id'),
            ],
        );

        $this->audit->record('projects', 'project.member-added', $project, after: [
            'administrator_id' => $administrator->id,
            'role' => $role,
            'can_view_financials' => $canViewFinancials,
        ]);

        return $member;
    }

    public function remove(Project $project, ProjectMember $member): void
    {
        abort_unless((int) $member->project_id === (int) $project->id, 404);

        if ((int) $project->owner_admin_id === (int) $member->organization_admin_user_id) {
            throw ValidationException::withMessages([
                'member' => 'The project owner cannot be removed.',
            ]);
        }

        $member->forceFill(['is_active' => false])->save();
        $this->audit->record('projects', 'project.member-removed', $project, before: [
            'administrator_id' => $member->organization_admin_user_id,
            'role' => $member->role,
        ]);
    }
}
