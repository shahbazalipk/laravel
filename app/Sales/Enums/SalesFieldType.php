<?php

namespace App\Sales\Enums;

enum SalesFieldType: string
{
    case Text = 'text';
    case Textarea = 'textarea';
    case Email = 'email';
    case Phone = 'phone';
    case Number = 'number';
    case Date = 'date';
    case Url = 'url';
    case Boolean = 'boolean';
    case Select = 'select';
    case Radio = 'radio';
    case Checkbox = 'checkbox';
    case Upload = 'upload';

    public function label(): string
    {
        return match ($this) {
            self::Text => 'Short text',
            self::Textarea => 'Long text',
            self::Email => 'Email',
            self::Phone => 'Phone',
            self::Number => 'Number',
            self::Date => 'Date',
            self::Url => 'URL',
            self::Boolean => 'Yes / No',
            self::Select => 'Dropdown',
            self::Radio => 'Radio',
            self::Checkbox => 'Checkboxes',
            self::Upload => 'File upload',
        };
    }

    public function hasOptions(): bool
    {
        return in_array($this, [self::Select, self::Radio, self::Checkbox], true);
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
