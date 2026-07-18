<?php

namespace App\Submissions\Enums;

enum WorkflowStageCategory: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Screening = 'screening';
    case Review = 'review';
    case Decision = 'decision';
    case Revision = 'revision';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Withdrawn = 'withdrawn';
}
