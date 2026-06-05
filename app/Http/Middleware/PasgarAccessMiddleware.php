<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PasgarAccessMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user || ! $user->tags()->whereIn('name', ['QA/QC', 'Supervisor'])->exists()) {
            abort(403);
        }

        return $next($request);
    }
}
