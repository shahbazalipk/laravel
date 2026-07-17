<?php

namespace App\Sales\Services;

use App\Sales\Models\SalesActivity;
use Illuminate\Database\Eloquent\Model;

class SalesActivityLogger
{
    public function log(Model $subject, string $type, string $summary, ?array $properties = null): void
    {
        SalesActivity::create([
            'event_id' => config('event.event_id'),
            'org_id' => config('event.org_id'),
            'subject_type' => $subject->getMorphClass(),
            'subject_id' => $subject->getKey(),
            'type' => $type,
            'summary' => $summary,
            'properties' => $properties,
            'actor_admin_id' => session('admin_id'),
        ]);
    }
}
