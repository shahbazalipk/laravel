<?php

namespace App\Shared\Notifications;

use App\Shared\Notifications\Models\PlatformNotification;

class NotificationService
{
    public function send(
        int $recipientAdminId,
        string $module,
        string $type,
        string $title,
        ?string $body = null,
        ?string $resourceType = null,
        ?string $resourcePublicId = null,
        array $data = [],
        ?string $deduplicationKey = null,
    ): PlatformNotification {
        $attributes = [
            'recipient_admin_id' => $recipientAdminId,
            'module' => $module,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'resource_type' => $resourceType,
            'resource_public_id' => $resourcePublicId,
            'data' => $data,
            'deduplication_key' => $deduplicationKey,
        ];

        if ($deduplicationKey === null) {
            return PlatformNotification::query()->create($attributes);
        }

        return PlatformNotification::query()->firstOrCreate(
            [
                'recipient_admin_id' => $recipientAdminId,
                'deduplication_key' => $deduplicationKey,
            ],
            $attributes,
        );
    }
}
