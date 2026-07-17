<?php

namespace App\Payments\Enums;

enum PaymentEntryStatus: string
{
    case Succeeded = 'succeeded';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Succeeded => 'Succeeded',
            self::Failed => 'Failed',
        };
    }
}
