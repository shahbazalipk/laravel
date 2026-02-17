<?php

namespace App\Services;

use App\Models\RegistrationStatus;
use App\Traits\HasAuditLogging;

class RegistrationStatusService
{
    use HasAuditLogging;

    public function getAllStatuses()
    {
        return RegistrationStatus::orderBy('sort_order')->orderBy('name')->get();
    }

    public function getActiveStatuses()
    {
        return RegistrationStatus::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function createStatus(array $data): RegistrationStatus
    {
        $status = RegistrationStatus::create($data);
        $this->logCreated($status, $data);
        return $status;
    }

    public function updateStatus(RegistrationStatus $status, array $data): RegistrationStatus
    {
        $oldData = $status->toArray();
        $status->update($data);
        $this->logUpdated($status, $oldData, $status->fresh()->toArray());
        return $status->fresh();
    }

    public function deleteStatus(RegistrationStatus $status): bool
    {
        $this->logDeleted($status);
        return $status->delete();
    }

    public function toggleActive(RegistrationStatus $status): RegistrationStatus
    {
        $status->is_active = !$status->is_active;
        $status->save();
        $this->logToggled($status, $status->is_active);
        return $status;
    }
}
