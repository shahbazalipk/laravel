<?php

namespace App\Services;

use App\Models\ExhibitorTag;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ExhibitorTagService
{
    public function __construct(
        private AuditService $auditService
    ) {}

    public function getAllExhibitorTags(): Collection
    {
        return ExhibitorTag::ordered()->get();
    }

    public function createExhibitorTag(array $data): ExhibitorTag
    {
        $data['event_id'] = config('event.event_id');
        $data['org_id'] = config('event.org_id');
        
        // Auto-generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $exhibitorTag = ExhibitorTag::create($data);

        $this->auditService->log(
            'created',
            $exhibitorTag,
            ['name' => $exhibitorTag->name],
            "Created exhibitor tag: {$exhibitorTag->name}"
        );

        return $exhibitorTag;
    }

    public function updateExhibitorTag(ExhibitorTag $exhibitorTag, array $data): ExhibitorTag
    {
        $oldName = $exhibitorTag->name;

        // Auto-generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $exhibitorTag->update($data);

        $this->auditService->log(
            'updated',
            $exhibitorTag,
            ['old_name' => $oldName, 'new_name' => $exhibitorTag->name],
            "Updated exhibitor tag: {$oldName} to {$exhibitorTag->name}"
        );

        return $exhibitorTag;
    }

    public function deleteExhibitorTag(ExhibitorTag $exhibitorTag): bool
    {
        $name = $exhibitorTag->name;

        $deleted = $exhibitorTag->delete();

        if ($deleted) {
            $this->auditService->log(
                'deleted',
                $exhibitorTag,
                ['name' => $name],
                "Deleted exhibitor tag: {$name}"
            );
        }

        return $deleted;
    }

    public function toggleActive(ExhibitorTag $exhibitorTag): ExhibitorTag
    {
        $exhibitorTag->is_active = !$exhibitorTag->is_active;
        $exhibitorTag->save();

        $status = $exhibitorTag->is_active ? 'activated' : 'deactivated';

        $this->auditService->log(
            'updated',
            $exhibitorTag,
            ['is_active' => $exhibitorTag->is_active],
            "Exhibitor tag {$status}: {$exhibitorTag->name}"
        );

        return $exhibitorTag;
    }
}
