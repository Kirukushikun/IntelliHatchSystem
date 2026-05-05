<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is not authenticated
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'Please login to access user panel.');
        }

        // Check if authenticated user is hatchery user (user_type === 2)
        if (((int) Auth::user()->user_type) !== 2) {
            return redirect('/admin/dashboard')->with('error', 'Access denied. User privileges required.');
        }

        return $next($request);
    }
}
