<?php

namespace App\Services;

use App\Models\ExhibitorType;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ExhibitorTypeService
{
    public function __construct(
        private AuditService $auditService
    ) {}

    public function getAllExhibitorTypes(): Collection
    {
        return ExhibitorType::ordered()->get();
    }

    public function createExhibitorType(array $data): ExhibitorType
    {
        $data['event_id'] = config('event.event_id');
        $data['org_id'] = config('event.org_id');
        
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $exhibitorType = ExhibitorType::create($data);

        $this->auditService->log(
            'created',
            $exhibitorType,
            ['name' => $exhibitorType->name],
            "Created exhibitor type: {$exhibitorType->name}"
        );

        return $exhibitorType;
    }

    public function updateExhibitorType(ExhibitorType $exhibitorType, array $data): ExhibitorType
    {
        $oldName = $exhibitorType->name;

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $exhibitorType->update($data);

        $this->auditService->log(
            'updated',
            $exhibitorType,
            ['old_name' => $oldName, 'new_name' => $exhibitorType->name],
            "Updated exhibitor type: {$oldName} to {$exhibitorType->name}"
        );

        return $exhibitorType;
    }

    public function deleteExhibitorType(ExhibitorType $exhibitorType): bool
    {
        $name = $exhibitorType->name;
        $deleted = $exhibitorType->delete();

        if ($deleted) {
            $this->auditService->log(
                'deleted',
                $exhibitorType,
                ['name' => $name],
                "Deleted exhibitor type: {$name}"
            );
        }

        return $deleted;
    }

    public function toggleActive(ExhibitorType $exhibitorType): ExhibitorType
    {
        $exhibitorType->is_active = !$exhibitorType->is_active;
        $exhibitorType->save();

        $status = $exhibitorType->is_active ? 'activated' : 'deactivated';

        $this->auditService->log(
            'updated',
            $exhibitorType,
            ['is_active' => $exhibitorType->is_active],
            "Exhibitor type {$status}: {$exhibitorType->name}"
        );

        return $exhibitorType;
    }
}
