<?php

namespace App\Forms\Enums;

enum FormQuestionType: string
{
    case Text = 'text';
    case Radio = 'radio';
    case Checkbox = 'checkbox';
    case Textarea = 'textarea';
    case Select = 'select';
    case Upload = 'upload';

    public function label(): string
    {
        return match ($this) {
            self::Text => 'Text',
            self::Radio => 'Radio',
            self::Checkbox => 'Checkbox',
            self::Textarea => 'Textarea',
            self::Select => 'Select',
            self::Upload => 'Upload',
        };
    }

    public function hasOptions(): bool
    {
        return match ($this) {
            self::Radio, self::Checkbox, self::Select => true,
            default => false,
        };
    }

    public function isUpload(): bool
    {
        return $this === self::Upload;
    }

    public function isMultiValue(): bool
    {
        return $this === self::Checkbox;
    }
}
