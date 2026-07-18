<?php

namespace App\Submissions\Enums;

enum QuestionType: string
{
    case Text = 'text';
    case Textarea = 'textarea';
    case RichText = 'rich_text';
    case Email = 'email';
    case Phone = 'phone';
    case Number = 'number';
    case Decimal = 'decimal';
    case Date = 'date';
    case DateTime = 'datetime';
    case Time = 'time';
    case Select = 'select';
    case MultiSelect = 'multi_select';
    case MultiselectLegacy = 'multiselect';
    case Radio = 'radio';
    case Checkbox = 'checkbox';
    case YesNo = 'yes_no';
    case Country = 'country';
    case City = 'city';
    case Url = 'url';
    case File = 'file';
    case Image = 'image';
    case Person = 'person';
    case Rating = 'rating';
    case Consent = 'consent';
    case Heading = 'heading';
    case Description = 'description';
    case Divider = 'divider';
    case Hidden = 'hidden';
    case Calculated = 'calculated';
    case Repeater = 'repeater';
}
