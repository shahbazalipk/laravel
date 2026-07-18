<?php

namespace App\Submissions\Enums;

enum SubmissionCategory: string
{
    case Abstract = 'abstract';
    case Session = 'session';
    case Speaker = 'speaker';
    case Workshop = 'workshop';
    case Poster = 'poster';
    case Panel = 'panel';
    case Other = 'other';
}
