<?php

namespace App\Http\Middleware;

use App\Submissions\Models\PortalUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubmissionPortalAuthenticated
{
    public function handle(Request $request, Closure $next, ?string $role = null): Response
    {
        $id = $request->session()->get('submission_portal_user_id');
        $user = $id ? PortalUser::query()->find($id) : null;
        if (! $user || $user->status !== 'active' || ($role && ! in_array($role, $user->roles ?? [], true))) {
            return redirect()->route('submissions.portal.login')->with('error', 'Sign in to continue.');
        }
        $request->attributes->set('submission_portal_user', $user);

        return $next($request);
    }
}
