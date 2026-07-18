<?php

namespace App\Projects\Services;

use App\Models\OrganizationAdminUser;
use App\Projects\Models\ProjectTask;
use App\Projects\Models\ProjectTeamMember;
use App\Projects\Models\ProjectUserAvailability;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class ProjectWorkloadService
{
    /** @return Collection<int, array<string, mixed>> */
    public function weekly(?CarbonImmutable $week = null): Collection
    {
        $start = ($week ?? CarbonImmutable::today())->startOfWeek();
        $end = $start->endOfWeek();
        $users = OrganizationAdminUser::query()
            ->where('organization_id', config('event.org_id'))
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return $users->map(function (OrganizationAdminUser $user) use ($start, $end): array {
            $tasks = ProjectTask::query()
                ->with('project')
                ->whereHas('assignees', fn ($query) => $query->whereKey($user->id))
                ->whereNotIn('status', ['done', 'cancelled'])
                ->where(function ($query) use ($start, $end): void {
                    $query->whereBetween('due_date', [$start, $end])
                        ->orWhereBetween('start_date', [$start, $end])
                        ->orWhere(fn ($range) => $range
                            ->where('start_date', '<=', $start)
                            ->where('due_date', '>=', $end));
                })
                ->get();
            $capacity = (int) (ProjectTeamMember::query()
                ->where('organization_admin_user_id', $user->id)
                ->where('is_active', true)
                ->max('weekly_capacity_minutes') ?? 2400);
            $availability = ProjectUserAvailability::query()
                ->where('organization_admin_user_id', $user->id)
                ->where('starts_on', '<=', $end)
                ->where('ends_on', '>=', $start)
                ->latest()
                ->first();
            if ($availability) {
                $capacity = $availability->is_available
                    ? (int) ($availability->available_minutes ?? $capacity)
                    : 0;
            }

            $assigned = (int) $tasks->sum('estimated_minutes');
            $utilization = $capacity > 0 ? (int) round(($assigned / $capacity) * 100) : ($assigned > 0 ? 999 : 0);

            return [
                'user' => $user,
                'tasks' => $tasks,
                'assigned_minutes' => $assigned,
                'capacity_minutes' => $capacity,
                'utilization_percent' => $utilization,
                'indicator' => match (true) {
                    $utilization === 0 => 'available',
                    $utilization <= 75 => 'balanced',
                    $utilization <= 100 => 'near_capacity',
                    default => 'overloaded',
                },
            ];
        });
    }
}
