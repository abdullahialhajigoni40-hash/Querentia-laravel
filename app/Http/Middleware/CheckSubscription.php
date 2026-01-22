<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        // If no user is logged in, allow access (auth middleware will handle this)
        if (!$user) {
            return $next($request);
        }
        
        // Check if subscription is required globally
        if (config('ai.requires_subscription', false)) {
            // Check if user has active subscription
            if (!$user->hasActiveSubscription()) {
                // Check free tier usage
                if ($this->hasExceededFreeTier($user)) {
                    return $this->handleSubscriptionRequired($request);
                }
            }
        }
        
        return $next($request);
    }
    
    /**
     * Check if user has exceeded free tier limits
     */
    private function hasExceededFreeTier($user): bool
    {
        if ($user->hasActiveSubscription()) {
            return false;
        }
        
        // Get free tier limit from config
        $freeLimit = config('ai.free_tier_limit', 10);
        
        // Count AI usage in current month
        $currentMonthUsage = $user->aiUsageLogs()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        
        return $currentMonthUsage >= $freeLimit;
    }
    
    /**
     * Handle subscription required response
     */
    private function handleSubscriptionRequired(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription required for this feature.',
                'error' => 'SUBSCRIPTION_REQUIRED',
                'upgrade_url' => route('subscription.upgrade')
            ], 403);
        }
        
        return redirect()->route('subscription.required')
            ->with('message', 'This feature requires an active subscription. Please upgrade to continue.');
    }
}