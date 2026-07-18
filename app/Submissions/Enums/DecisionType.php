<?php

namespace App\Submissions\Enums;

enum DecisionType: string
{
    case Accept = 'accept';
    case Selected = 'selected';
    case ConditionallySelected = 'conditionally_selected';
    case Reject = 'reject';
    case Rejected = 'rejected';
    case Waitlist = 'waitlist';
    case Waitlisted = 'waitlisted';
    case RequestRevision = 'request_revision';
    case RevisionRequired = 'revision_required';
    case Disqualified = 'disqualified';
    case Withdraw = 'withdraw';
    case Withdrawn = 'withdrawn';
}
