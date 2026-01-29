<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
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
            return redirect('/admin/login')->with('error', 'Please login to access the admin panel.');
        }

        // Check if authenticated user is admin (user_type === 0)
        if (((int) Auth::user()->user_type) !== 0) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect('/admin/login')->with('error', 'Access denied. Admin privileges required.');
        }

        return $next($request);
    }
}