<?php

namespace App\Payments\Enums;

enum RegistrationPaymentSummaryStatus: string
{
    case Pending = 'pending';
    case PartiallyPaid = 'partially_paid';
    case Paid = 'paid';
    case Overpaid = 'overpaid';
    case PartiallyRefunded = 'partially_refunded';
    case Refunded = 'refunded';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::PartiallyPaid => 'Partially Paid',
            self::Paid => 'Paid',
            self::Overpaid => 'Overpaid',
            self::PartiallyRefunded => 'Partially Refunded',
            self::Refunded => 'Refunded',
            self::Failed => 'Failed',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Paid => 'bg-green-100 text-green-800',
            self::Pending => 'bg-yellow-100 text-yellow-800',
            self::Failed => 'bg-red-100 text-red-800',
            self::Refunded, self::PartiallyRefunded => 'bg-gray-100 text-gray-800',
            self::PartiallyPaid => 'bg-amber-100 text-amber-800',
            self::Overpaid => 'bg-blue-100 text-blue-800',
        };
    }
}
