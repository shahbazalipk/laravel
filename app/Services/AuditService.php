<?php

namespace App\Services;

use App\Models\ActivityLog;

class AuditService
{
    public function log(string $action, $subject, array $properties = [], ?string $description = null): void
    {
        $adminId = session('admin_id');
        $adminEmail = session('admin_email');
        
        $logData = [
            'log_name' => $this->getLogName($subject),
            'description' => $description ?? $this->generateDescription($action, $subject),
            'subject_type' => get_class($subject),
            'subject_id' => $subject->id,
            'causer_type' => 'App\Models\Admin',
            'causer_id' => $adminId,
            'created_by' => $adminId,
            'properties' => array_merge($properties, [
                'admin_id' => $adminId,
                'admin_email' => $adminEmail,
                'action' => $action,
                'timestamp' => now()->toDateTimeString(),
            ]),
        ];

        // Set updated_by for update and toggle actions
        if (in_array($action, ['updated', 'toggled'])) {
            $logData['updated_by'] = $adminId;
        }

        // Set deleted_by for delete actions
        if ($action === 'deleted') {
            $logData['deleted_by'] = $adminId;
        }

        ActivityLog::create($logData);
    }

    private function getLogName($subject): string
    {
        $className = class_basename($subject);
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className));
    }

    private function generateDescription(string $action, $subject): string
    {
        $modelName = class_basename($subject);
        $identifier = $subject->name ?? $subject->code ?? $subject->id;
        
        return match($action) {
            'created' => "{$modelName} '{$identifier}' was created",
            'updated' => "{$modelName} '{$identifier}' was updated",
            'deleted' => "{$modelName} '{$identifier}' was deleted",
            'toggled' => "{$modelName} '{$identifier}' status was toggled",
            'imported' => "Codes were imported to {$modelName} '{$identifier}'",
            default => "{$modelName} '{$identifier}' - {$action}",
        };
    }

    public function logCreated($subject, array $attributes = []): void
    {
        $this->log('created', $subject, [
            'attributes' => $attributes,
        ]);
    }

    public function logUpdated($subject, array $old = [], array $new = []): void
    {
        $this->log('updated', $subject, [
            'old' => $old,
            'new' => $new,
        ]);
    }

    public function logDeleted($subject): void
    {
        $this->log('deleted', $subject, [
            'deleted_at' => now()->toDateTimeString(),
        ]);
    }

    public function logToggled($subject, bool $newStatus): void
    {
        $this->log('toggled', $subject, [
            'is_active' => $newStatus,
        ]);
    }

    public function logImport($subject, int $count): void
    {
        $this->log('imported', $subject, [
            'imported_count' => $count,
        ]);
    }
}
