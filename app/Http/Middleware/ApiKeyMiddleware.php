<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('x-api-key');
        
        if (!$apiKey || $apiKey !== env('API_KEY')) {
            return response()->json([
                'error' => 'Invalid or missing API key',
                'message' => 'Please provide a valid API key in the x-api-key header'
            ], 401);
        }

        return $next($request);
    }
}
