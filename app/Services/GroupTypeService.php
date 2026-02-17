<?php

namespace App\Services;

use App\Models\GroupType;
use Illuminate\Support\Str;

class GroupTypeService
{
    public function __construct(
        private AuditService $auditService
    ) {}

    public function getAllGroupTypes()
    {
        return GroupType::orderBy('sort_order')->orderBy('name')->get();
    }

    public function createGroupType(array $data): GroupType
    {
        $data['event_id'] = config('event.event_id');
        $data['org_id'] = config('event.org_id');
        
        if (empty($data['slug']) && !empty($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $groupType = GroupType::create($data);

        $this->auditService->log('group_type_created', GroupType::class, $groupType->id, [
            'name' => $groupType->name,
        ]);

        return $groupType;
    }

    public function updateGroupType(GroupType $groupType, array $data): GroupType
    {
        $oldData = $groupType->toArray();

        if (empty($data['slug']) && !empty($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $groupType->update($data);

        $this->auditService->log('group_type_updated', GroupType::class, $groupType->id, [
            'old' => $oldData,
            'new' => $groupType->fresh()->toArray(),
        ]);

        return $groupType;
    }

    public function deleteGroupType(GroupType $groupType): bool
    {
        $this->auditService->log('group_type_deleted', GroupType::class, $groupType->id, [
            'name' => $groupType->name,
        ]);

        return $groupType->delete();
    }

    public function toggleActive(GroupType $groupType): GroupType
    {
        $groupType->is_active = !$groupType->is_active;
        $groupType->save();

        $this->auditService->log('group_type_toggled', GroupType::class, $groupType->id, [
            'is_active' => $groupType->is_active,
        ]);

        return $groupType;
    }
}
