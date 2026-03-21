<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SuperadminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'Please login to continue.');
        }

        if ((int) Auth::user()->user_type !== 0) {
            return redirect('/admin/dashboard')->with('error', 'Access denied. Superadmin privileges required.');
        }

        return $next($request);
    }
}
