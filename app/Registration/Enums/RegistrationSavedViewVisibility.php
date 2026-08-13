<?php

namespace App\Registration\Enums;

enum RegistrationSavedViewVisibility: string
{
    case Private = 'private';
    case Public = 'public';
    case Users = 'users';

    public function label(): string
    {
        return match ($this) {
            self::Private => 'Private',
            self::Public => 'Public — all event users',
            self::Users => 'Specific people',
        };
    }
}
