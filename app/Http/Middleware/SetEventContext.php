<?php

namespace App\Http\Middleware;

use App\Services\EventContextService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetEventContext
{
    public function __construct(
        private EventContextService $eventContext
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $this->eventContext->bootFromSession();

        if (!config('event.event_id') && ($context = $this->eventContext->resolveFromRequest($request))) {
            $this->eventContext->apply($context['event_id'], $context['organization_id']);
        }

        return $next($request);
    }
}
