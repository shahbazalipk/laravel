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
            self::Information => 2,
            self::Category => 3,
            self::Confirmation => 4,
        };
    }

    public function next(): ?self
    {
        return match ($this) {
            self::Email => self::Information,
            self::Information => self::Category,
            self::Category => self::Confirmation,
            self::Confirmation => null,
        };
    }

    public function previous(): ?self
    {
        return match ($this) {
            self::Email => null,
            self::Information => self::Email,
            self::Category => self::Information,
            self::Confirmation => self::Category,
        };
    }

    /**
     * @return list<self>
     */
    public static function ordered(): array
    {
        return [
            self::Email,
            self::Information,
            self::Category,
            self::Confirmation,
        ];
    }

    public function canAccessFrom(self $current): bool
    {
        return $this->number() <= $current->number();
    }
}
