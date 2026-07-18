<?php

namespace App\Projects\Services;

use App\Projects\Models\ProjectSavedFilter;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ProjectSavedFilterService
{
    private const ALLOWED_CRITERIA = [
        'project_id',
        'team_id',
        'assignee_id',
        'status',
        'priority',
        'type',
        'due',
        'label',
        'overdue',
        'blocked',
        'unassigned',
    ];

    public function save(array $data): ProjectSavedFilter
    {
        return DB::transaction(function () use ($data): ProjectSavedFilter {
            if ($data['is_default'] ?? false) {
                ProjectSavedFilter::query()
                    ->where('organization_admin_user_id', session('admin_id'))
                    ->where('view', $data['view'])
                    ->update(['is_default' => false]);
            }

            return ProjectSavedFilter::query()->updateOrCreate(
                [
                    'organization_admin_user_id' => session('admin_id'),
                    'name' => $data['name'],
                    'view' => $data['view'],
                ],
                [
                    'criteria' => Arr::only($data['criteria'] ?? [], self::ALLOWED_CRITERIA),
                    'visible_columns' => $data['visible_columns'] ?? null,
                    'visibility' => $data['visibility'] ?? 'private',
                    'is_default' => (bool) ($data['is_default'] ?? false),
                ],
            );
        });
    }
}
