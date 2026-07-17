<?php

namespace App\Forms\Enums;

enum FormConditionAction: string
{
    case Show = 'show';
    case Hide = 'hide';

    public function label(): string
    {
        return match ($this) {
            self::Show => 'Show',
            self::Hide => 'Hide',
        };
    }
}
