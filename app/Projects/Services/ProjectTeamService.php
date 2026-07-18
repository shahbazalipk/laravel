<?php

namespace App\Projects\Services;

use App\Models\OrganizationAdminUser;
use App\Projects\Models\ProjectTeam;
use App\Projects\Models\ProjectTeamMember;
use App\Shared\Audit\AuditLogger;
use App\Shared\Tenancy\TenantContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProjectTeamService
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function create(array $data): ProjectTeam
    {
        $code = strtoupper((string) $data['code']);

        if (! preg_match('/^[A-Z][A-Z0-9-]{1,15}$/', $code)) {
            throw ValidationException::withMessages([
                'code' => 'Use 2–16 uppercase letters, numbers or hyphens, starting with a letter.',
            ]);
        }

        return DB::transaction(function () use ($data, $code): ProjectTeam {
            $team = ProjectTeam::query()->create([
                ...$data,
                'code' => $code,
                'lead_admin_id' => $data['lead_admin_id'] ?? session('admin_id'),
                'created_by' => session('admin_id'),
                'is_active' => true,
            ]);

            $this->addMember($team, (int) $team->lead_admin_id, 'lead');
            $this->audit->record('projects', 'team.created', $team, after: [
                'name' => $team->name,
                'code' => $team->code,
            ]);

            return $team->load('members.administrator');
        });
    }

    public function addMember(ProjectTeam $team, int $administratorId, string $role): ProjectTeamMember
    {
        $administrator = $this->administrator($administratorId);

        $member = ProjectTeamMember::query()->updateOrCreate(
            [
                'team_id' => $team->id,
                'organization_admin_user_id' => $administrator->id,
            ],
            [
                'role' => $role,
                'is_active' => true,
            ],
        );

        $this->audit->record('projects', 'team.member-added', $team, after: [
            'administrator_id' => $administrator->id,
            'role' => $role,
        ]);

        return $member;
    }

    public function removeMember(ProjectTeam $team, ProjectTeamMember $member): void
    {
        abort_unless((int) $member->team_id === (int) $team->id, 404);

        if ((int) $team->lead_admin_id === (int) $member->organization_admin_user_id) {
            throw ValidationException::withMessages([
                'member' => 'Assign another team lead before removing this member.',
            ]);
        }

        $member->forceFill(['is_active' => false])->save();
        $this->audit->record('projects', 'team.member-removed', $team, before: [
            'administrator_id' => $member->organization_admin_user_id,
            'role' => $member->role,
        ]);
    }

    private function administrator(int $id): OrganizationAdminUser
    {
        $organizationId = TenantContext::fromConfig()->organizationId;

        $administrator = OrganizationAdminUser::query()->findOrFail($id);

        if ((int) $administrator->organization_id !== $organizationId) {
            throw ValidationException::withMessages([
                'organization_admin_user_id' => 'The selected administrator is outside this organization.',
            ]);
        }

        return $administrator;
    }
}
