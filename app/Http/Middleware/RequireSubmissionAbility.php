<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireSubmissionAbility
{
    public function handle(Request $request, Closure $next, string $ability): Response
    {
        abort_unless(session('admin_logged_in'), 403);
        $abilities = session('submission_abilities');
        if (is_array($abilities)) {
            abort_unless(in_array('*', $abilities, true) || in_array($ability, $abilities, true), 403);
        }

        return $next($request);
    }
}
