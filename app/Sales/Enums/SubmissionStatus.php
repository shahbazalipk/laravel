<?php

namespace App\Sales\Enums;

enum SubmissionStatus: string
{
    case New = 'new';
    case InReview = 'in_review';
    case Contacted = 'contacted';
    case Qualified = 'qualified';
    case Converted = 'converted';
    case Rejected = 'rejected';
    case Spam = 'spam';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::InReview => 'In Review',
            self::Contacted => 'Contacted',
            self::Qualified => 'Qualified',
            self::Converted => 'Converted to Deal',
            self::Rejected => 'Rejected',
            self::Spam => 'Spam',
            self::Archived => 'Archived',
        };
    }
}
