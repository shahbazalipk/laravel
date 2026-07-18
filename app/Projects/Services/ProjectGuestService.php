<?php

namespace App\Projects\Services;

use App\Projects\Models\Project;
use App\Projects\Models\ProjectGuest;
use App\Projects\Models\ProjectGuestAccess;
use App\Projects\Models\ProjectTask;
use App\Shared\Audit\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProjectGuestService
{
    public function __construct(private readonly AuditLogger $audit) {}

    /** @return array{guest:ProjectGuest,token:string} */
    public function invite(Project $project, array $data): array
    {
        $task = isset($data['task_id'])
            ? ProjectTask::query()->where('project_id', $project->id)->findOrFail($data['task_id'])
            : null;
        $token = Str::random(64);

        return DB::transaction(function () use ($project, $task, $data, $token): array {
            $guest = ProjectGuest::query()->updateOrCreate(
                ['email' => strtolower($data['email'])],
                [
                    'name' => $data['name'],
                    'type' => $data['type'],
                    'organization_name' => $data['organization_name'] ?? null,
                    'token_hash' => hash('sha256', $token),
                    'invitation_expires_at' => now()->addDays((int) ($data['invitation_days'] ?? 7)),
                    'status' => 'invited',
                    'invited_by' => session('admin_id'),
                ],
            );

            ProjectGuestAccess::query()->updateOrCreate(
                [
                    'guest_id' => $guest->id,
                    'project_id' => $project->id,
                    'task_id' => $task?->id,
                ],
                [
                    'role' => $data['role'],
                    'abilities' => $this->abilities($data['role']),
                    'expires_at' => $data['access_expires_at'] ?? null,
                    'granted_by' => session('admin_id'),
                ],
            );

            $this->audit->record('projects', 'guest.invited', $guest, after: [
                'project' => $project->public_id,
                'task' => $task?->public_id,
                'role' => $data['role'],
                'invitation_expires_at' => $guest->invitation_expires_at,
            ]);

            return ['guest' => $guest->load('accessGrants.project'), 'token' => $token];
        });
    }

    public function authenticate(string $token): ProjectGuest
    {
        $guest = ProjectGuest::query()
            ->where('token_hash', hash('sha256', $token))
            ->where('status', 'invited')
            ->firstOrFail();

        if (! $guest->invitation_expires_at || $guest->invitation_expires_at->isPast()) {
            throw ValidationException::withMessages(['token' => 'This guest invitation has expired.']);
        }

        $guest->forceFill([
            'status' => 'active',
            'accepted_at' => $guest->accepted_at ?? now(),
            'last_accessed_at' => now(),
            'token_hash' => null,
        ])->save();

        return $guest;
    }

    /** @return list<string> */
    private function abilities(string $role): array
    {
        return match ($role) {
            'contributor' => ['view', 'comment', 'update-status'],
            'reviewer' => ['view', 'comment', 'review'],
            'viewer' => ['view'],
            default => throw ValidationException::withMessages(['role' => 'Invalid guest role.']),
        };
    }
}
