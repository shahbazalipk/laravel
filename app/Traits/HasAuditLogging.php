<?php

namespace App\Traits;

use App\Services\AuditService;

trait HasAuditLogging
{
    protected function getAuditService(): AuditService
    {
        return app(AuditService::class);
    }

    protected function logCreated($model, array $attributes = []): void
    {
        $this->getAuditService()->logCreated($model, $attributes);
    }

    protected function logUpdated($model, array $old = [], array $new = []): void
    {
        $this->getAuditService()->logUpdated($model, $old, $new);
    }

    protected function logDeleted($model): void
    {
        $this->getAuditService()->logDeleted($model);
    }

    protected function logToggled($model, bool $newStatus): void
    {
        $this->getAuditService()->logToggled($model, $newStatus);
    }
}
