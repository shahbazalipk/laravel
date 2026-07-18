<?php

namespace App\Http\Middleware;

use App\Shared\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireTenantContext
{
    public function handle(Request $request, Closure $next): Response
    {
        TenantContext::fromConfig();

        return $next($request);
    }
}
