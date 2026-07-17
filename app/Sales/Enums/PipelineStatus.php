<?php

namespace App\Sales\Enums;

enum PipelineStatus: string
{
    case Active = 'active';
    case Archived = 'archived';
    case Draft = 'draft';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Archived => 'Archived',
            self::Draft => 'Draft',
        };
    }
}
