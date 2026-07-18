<?php

namespace App\Registration\Enums;

enum RegistrationWizardStep: string
{
    case Email = 'email';
    case Information = 'information';
    case Category = 'category';
    case Confirmation = 'confirmation';

    public function label(): string
    {
        return match ($this) {
            self::Email => 'Email',
            self::Information => 'Information',
            self::Category => 'Category',
            self::Confirmation => 'Confirmation',
        };
    }

    public function number(): int
    {
        return match ($this) {
            self::Email => 1,
            self::Category => 2,
            self::Information => 3,
            self::Confirmation => 4,
        };
    }

    public function next(): ?self
    {
        return match ($this) {
            self::Email => self::Category,
            self::Category => self::Information,
            self::Information => self::Confirmation,
            self::Confirmation => null,
        };
    }

    public function previous(): ?self
    {
        return match ($this) {
            self::Email => null,
            self::Category => self::Email,
            self::Information => self::Category,
            self::Confirmation => self::Information,
        };
    }

    /**
     * @return list<self>
     */
    public static function ordered(): array
    {
        return [
            self::Email,
            self::Category,
            self::Information,
            self::Confirmation,
        ];
    }

    public function canAccessFrom(self $current): bool
    {
        return $this->number() <= $current->number();
    }
}
