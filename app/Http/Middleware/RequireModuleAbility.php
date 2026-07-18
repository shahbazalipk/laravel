<?php

namespace App\Http\Middleware;

use App\Shared\Authorization\ModuleAuthorizer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireModuleAbility
{
    public function __construct(
        private ModuleAuthorizer $authorizer,
    ) {}

    public function handle(Request $request, Closure $next, string $module, string $ability): Response
    {
        $this->authorizer->authorize($module, $ability);

        return $next($request);
    }
}
