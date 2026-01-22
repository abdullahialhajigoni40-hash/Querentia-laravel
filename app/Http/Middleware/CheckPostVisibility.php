<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\NetworkPost;
use Symfony\Component\HttpFoundation\Response;

class CheckPostVisibility
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $postId = $request->route('post') ?? $request->input('post_id');
        
        if (!$postId) {
            return $next($request);
        }
        
        // Get post
        $post = $request->route('post') instanceof NetworkPost 
            ? $request->route('post') 
            : NetworkPost::find($postId);
        
        if (!$post) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Post not found.',
                    'error' => 'POST_NOT_FOUND'
                ], 404);
            }
            
            abort(404, 'Post not found.');
        }
        
        // Check if user can view this post
        if (!$post->canView(Auth::user())) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view this post.',
                    'error' => 'POST_ACCESS_DENIED'
                ], 403);
            }
            
            abort(403, 'You do not have permission to view this post.');
        }
        
        return $next($request);
    }
}