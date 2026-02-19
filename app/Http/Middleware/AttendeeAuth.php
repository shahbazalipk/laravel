<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AttendeeAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!Session::has('attendee_id')) {
            return redirect()->route('attendee.login')
                ->with('error', 'Please login to access this page');
        }

        return $next($request);
    }
}
