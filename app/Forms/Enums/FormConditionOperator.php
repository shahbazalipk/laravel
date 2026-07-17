<?php

namespace App\Forms\Enums;

enum FormConditionOperator: string
{
    case Equals = 'equals';
    case NotEquals = 'not_equals';
    case Contains = 'contains';
    case IsEmpty = 'is_empty';
    case IsNotEmpty = 'is_not_empty';

    public function label(): string
    {
        return match ($this) {
            self::Equals => 'Equals',
            self::NotEquals => 'Not equals',
            self::Contains => 'Contains',
            self::IsEmpty => 'Is empty',
            self::IsNotEmpty => 'Is not empty',
        };
    }

    public function requiresCompareValue(): bool
    {
        return match ($this) {
            self::IsEmpty, self::IsNotEmpty => false,
            default => true,
        };
    }
}
