<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class AIRateLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = 'ai_rate_limit:' . ($request->user()?->id ?: $request->ip());
        $maxAttempts = config('ai.rate_limit', 100);
        $decayMinutes = config('ai.rate_limit_period', 60);
        
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "Too many AI requests. Please try again in {$seconds} seconds.",
                    'error' => 'RATE_LIMIT_EXCEEDED',
                    'retry_after' => $seconds
                ], 429);
            }
            
            return back()
                ->with('error', "Too many AI requests. Please try again in {$seconds} seconds.")
                ->setStatusCode(429);
        }
        
        RateLimiter::hit($key, $decayMinutes * 60);
        
        // Add rate limit headers to response
        $response = $next($request);
        
        $response->headers->add([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => RateLimiter::remaining($key, $maxAttempts),
            'X-RateLimit-Reset' => now()->addSeconds(RateLimiter::availableIn($key))->timestamp,
        ]);
        
        return $response;
    }
}