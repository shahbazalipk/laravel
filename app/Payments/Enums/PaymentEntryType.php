<?php

namespace App\Payments\Enums;

enum PaymentEntryType: string
{
    case Payment = 'payment';
    case Refund = 'refund';
    case Reversal = 'reversal';

    public function label(): string
    {
        return match ($this) {
            self::Payment => 'Payment',
            self::Refund => 'Refund',
            self::Reversal => 'Reversal',
        };
    }
}
