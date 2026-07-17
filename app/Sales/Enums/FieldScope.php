<?php

namespace App\Sales\Enums;

enum FieldScope: string
{
    case Pipeline = 'pipeline';
    case Deal = 'deal';

    public function label(): string
    {
        return match ($this) {
            self::Pipeline => 'Pipeline-level',
            self::Deal => 'Deal-level',
        };
    }
}
