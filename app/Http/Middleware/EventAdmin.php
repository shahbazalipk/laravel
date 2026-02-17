<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EventAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!session()->has('admin_logged_in')) {
            return redirect()->route('admin.login')
                ->with('error', 'Please login to access admin panel');
        }
        
        return $next($request);
    }
}
