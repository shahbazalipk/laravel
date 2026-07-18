<?php

namespace App\Http\Middleware;

use App\Models\Event;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubmissionModuleEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(config('submissions.enabled'), 404);
        if (! config('event.event_id') || ! config('event.org_id')) {
            abort_unless(app()->environment(['local', 'testing']), 404);
            $event = Event::getCurrentEvent();
            abort_unless($event && ($event->organization_id ?? $event->org_id), 404);
            config([
                'event.event_id' => $event->getKey(),
                'event.org_id' => $event->organization_id ?? $event->org_id,
            ]);
            app()->instance('current.event', $event);
        }

        return $next($request);
    }
}
