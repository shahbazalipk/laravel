<?php

namespace App\Services;

use App\Models\BusinessActivity;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BusinessActivityService
{
    public function __construct(
        private AuditService $auditService
    ) {}

    public function getAllBusinessActivities(): Collection
    {
        return BusinessActivity::ordered()->get();
    }

    public function createBusinessActivity(array $data): BusinessActivity
    {
        $data['event_id'] = config('event.event_id');
        $data['org_id'] = config('event.org_id');
        
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $businessActivity = BusinessActivity::create($data);

        $this->auditService->log(
            'created',
            $businessActivity,
            ['name' => $businessActivity->name],
            "Created business activity: {$businessActivity->name}"
        );

        return $businessActivity;
    }

    public function updateBusinessActivity(BusinessActivity $businessActivity, array $data): BusinessActivity
    {
        $oldName = $businessActivity->name;

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $businessActivity->update($data);

        $this->auditService->log(
            'updated',
            $businessActivity,
            ['old_name' => $oldName, 'new_name' => $businessActivity->name],
            "Updated business activity: {$oldName} to {$businessActivity->name}"
        );

        return $businessActivity;
    }

    public function deleteBusinessActivity(BusinessActivity $businessActivity): bool
    {
        $name = $businessActivity->name;
        $deleted = $businessActivity->delete();

        if ($deleted) {
            $this->auditService->log(
                'deleted',
                $businessActivity,
                ['name' => $name],
                "Deleted business activity: {$name}"
            );
        }

        return $deleted;
    }

    public function toggleActive(BusinessActivity $businessActivity): BusinessActivity
    {
        $businessActivity->is_active = !$businessActivity->is_active;
        $businessActivity->save();

        $status = $businessActivity->is_active ? 'activated' : 'deactivated';

        $this->auditService->log(
            'updated',
            $businessActivity,
            ['is_active' => $businessActivity->is_active],
            "Business activity {$status}: {$businessActivity->name}"
        );

        return $businessActivity;
    }
}
