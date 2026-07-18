<?php

namespace App\Submissions\Enums;

enum ReviewStatus: string
{
    case Draft = 'draft';
    case InProgress = 'in_progress';
    case Submitted = 'submitted';
    case Returned = 'returned';
    case Superseded = 'superseded';
}
