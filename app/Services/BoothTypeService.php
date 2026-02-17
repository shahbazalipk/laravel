<?php

namespace App\Services;

use App\Models\BoothType;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BoothTypeService
{
    public function __construct(
        private AuditService $auditService
    ) {}

    public function getAllBoothTypes(): Collection
    {
        return BoothType::ordered()->get();
    }

    public function createBoothType(array $data): BoothType
    {
        $data['event_id'] = config('event.event_id');
        $data['org_id'] = config('event.org_id');
        
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $boothType = BoothType::create($data);

        $this->auditService->log(
            'created',
            $boothType,
            ['name' => $boothType->name],
            "Created booth type: {$boothType->name}"
        );

        return $boothType;
    }

    public function updateBoothType(BoothType $boothType, array $data): BoothType
    {
        $oldName = $boothType->name;

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $boothType->update($data);

        $this->auditService->log(
            'updated',
            $boothType,
            ['old_name' => $oldName, 'new_name' => $boothType->name],
            "Updated booth type: {$oldName} to {$boothType->name}"
        );

        return $boothType;
    }

    public function deleteBoothType(BoothType $boothType): bool
    {
        $name = $boothType->name;
        $deleted = $boothType->delete();

        if ($deleted) {
            $this->auditService->log(
                'deleted',
                $boothType,
                ['name' => $name],
                "Deleted booth type: {$name}"
            );
        }

        return $deleted;
    }

    public function toggleActive(BoothType $boothType): BoothType
    {
        $boothType->is_active = !$boothType->is_active;
        $boothType->save();

        $status = $boothType->is_active ? 'activated' : 'deactivated';

        $this->auditService->log(
            'updated',
            $boothType,
            ['is_active' => $boothType->is_active],
            "Booth type {$status}: {$boothType->name}"
        );

        return $boothType;
    }
}
