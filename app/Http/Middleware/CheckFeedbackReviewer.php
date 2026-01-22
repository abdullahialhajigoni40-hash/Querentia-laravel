<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ReviewFeedback;
use Symfony\Component\HttpFoundation\Response;

class CheckFeedbackReviewer
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $feedbackId = $request->route('feedback') ?? $request->input('feedback_id');
        
        if (!$feedbackId) {
            return $next($request);
        }
        
        // Get feedback
        $feedback = $request->route('feedback') instanceof ReviewFeedback 
            ? $request->route('feedback') 
            : ReviewFeedback::find($feedbackId);
        
        if (!$feedback) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Feedback not found.',
                    'error' => 'FEEDBACK_NOT_FOUND'
                ], 404);
            }
            
            abort(404, 'Feedback not found.');
        }
        
        // Check if user is the feedback author or has permission
        $user = Auth::user();
        
        if ($feedback->user_id !== $user->id && 
            $feedback->journal->user_id !== $user->id && 
            !$user->is_admin) {
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to modify this feedback.',
                    'error' => 'FEEDBACK_ACCESS_DENIED'
                ], 403);
            }
            
            abort(403, 'You do not have permission to modify this feedback.');
        }
        
        return $next($request);
    }
}