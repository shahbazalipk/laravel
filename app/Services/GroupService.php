<?php

namespace App\Services;

use App\Models\Group;

class GroupService
{
    public function __construct(
        private AuditService $auditService
    ) {}

    public function getAllGroups()
    {
        return Group::with(['groupType', 'industry', 'tags'])
            ->orderBy('group_name')
            ->get();
    }

    public function createGroup(array $data): Group
    {
        $data['event_id'] = config('event.event_id');
        $data['org_id'] = config('event.org_id');

        // Extract tags for sync
        $tags = $data['tags'] ?? [];
        unset($data['tags']);

        $group = Group::create($data);

        // Sync tags
        if (!empty($tags)) {
            $group->tags()->sync($tags);
        }

        $this->auditService->log('group_created', Group::class, $group->id, [
            'group_name' => $group->group_name,
        ]);

        return $group->load(['groupType', 'industry', 'tags']);
    }

    public function updateGroup(Group $group, array $data): Group
    {
        $oldData = $group->toArray();

        // Extract tags for sync
        $tags = $data['tags'] ?? [];
        unset($data['tags']);

        $group->update($data);

        // Sync tags
        $group->tags()->sync($tags);

        $this->auditService->log('group_updated', Group::class, $group->id, [
            'old' => $oldData,
            'new' => $group->fresh()->toArray(),
        ]);

        return $group->load(['groupType', 'industry', 'tags']);
    }

    public function deleteGroup(Group $group): bool
    {
        $this->auditService->log('group_deleted', Group::class, $group->id, [
            'group_name' => $group->group_name,
        ]);

        return $group->delete();
    }

    public function toggleActive(Group $group): Group
    {
        $group->is_active = !$group->is_active;
        $group->save();

        $this->auditService->log('group_toggled', Group::class, $group->id, [
            'is_active' => $group->is_active,
        ]);

        return $group;
    }

    public function toggleVip(Group $group): Group
    {
        $group->is_vip = !$group->is_vip;
        $group->save();

        $this->auditService->log('group_vip_toggled', Group::class, $group->id, [
            'is_vip' => $group->is_vip,
        ]);

        return $group;
    }
}
