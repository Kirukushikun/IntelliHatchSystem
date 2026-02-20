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
        $requiredKey = config('services.api.key');
        
        // If API_KEY is not configured, deny all requests
        if (empty($requiredKey)) {
            return response()->json([
                'error' => 'Internal server error',
                'message' => 'Internal server error'
            ], 500, [], JSON_PRETTY_PRINT);
        }
        
        if (!is_string($apiKey) || $apiKey === '' || !hash_equals((string) $requiredKey, $apiKey)) {
            return response()->json([
                'error' => 'Invalid or missing API key',
                'message' => 'Please provide a valid API key in the x-api-key header'
            ], 401, [], JSON_PRETTY_PRINT);
        }

        return $next($request);
    }
}
