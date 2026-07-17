<?php

namespace App\Sales\Enums;

enum InquiryFormStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Unpublished = 'unpublished';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Published => 'Published',
            self::Unpublished => 'Unpublished',
            self::Archived => 'Archived',
        };
    }

    public function isPubliclyAvailable(): bool
    {
        return $this === self::Published;
    }
}
