<?php

namespace App\Forms\Enums;

enum FormAudience: string
{
    case Registration = 'registration';
    case Exhibitor = 'exhibitor';
    case Group = 'group';

    public function label(): string
    {
        return match ($this) {
            self::Registration => 'Registration',
            self::Exhibitor => 'Exhibitor',
            self::Group => 'Group',
        };
    }
}
