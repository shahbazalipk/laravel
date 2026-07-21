<?php

namespace App\Registration\Enums;

enum RegistrationFormat: string
{
    case MultiStep = 'multi_step';
    case SinglePage = 'single_page';

    public function label(): string
    {
        return match ($this) {
            self::MultiStep => 'Multi-step wizard',
            self::SinglePage => 'Single-page form',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::MultiStep => 'Guide registrants through email, category, information, and confirmation steps.',
            self::SinglePage => 'Show category, personal details, and confirmation on one form.',
        };
    }

    /**
     * @return list<self>
     */
    public static function options(): array
    {
        return self::cases();
    }
}
