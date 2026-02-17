<?php

namespace App\Services;

use App\Models\Industry;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class IndustryService
{
    public function __construct(
        private AuditService $auditService
    ) {}

    public function getAllIndustries(): Collection
    {
        return Industry::ordered()->get();
    }

    public function createIndustry(array $data): Industry
    {
        $data['event_id'] = config('event.event_id');
        $data['org_id'] = config('event.org_id');
        
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $industry = Industry::create($data);

        $this->auditService->log(
            'created',
            $industry,
            ['name' => $industry->name],
            "Created industry: {$industry->name}"
        );

        return $industry;
    }

    public function updateIndustry(Industry $industry, array $data): Industry
    {
        $oldName = $industry->name;

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $industry->update($data);

        $this->auditService->log(
            'updated',
            $industry,
            ['old_name' => $oldName, 'new_name' => $industry->name],
            "Updated industry: {$oldName} to {$industry->name}"
        );

        return $industry;
    }

    public function deleteIndustry(Industry $industry): bool
    {
        $name = $industry->name;
        $deleted = $industry->delete();

        if ($deleted) {
            $this->auditService->log(
                'deleted',
                $industry,
                ['name' => $name],
                "Deleted industry: {$name}"
            );
        }

        return $deleted;
    }

    public function toggleActive(Industry $industry): Industry
    {
        $industry->is_active = !$industry->is_active;
        $industry->save();

        $status = $industry->is_active ? 'activated' : 'deactivated';

        $this->auditService->log(
            'updated',
            $industry,
            ['is_active' => $industry->is_active],
            "Industry {$status}: {$industry->name}"
        );

        return $industry;
    }
}
