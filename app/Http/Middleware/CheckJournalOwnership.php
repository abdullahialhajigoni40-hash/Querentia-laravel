<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Journal;
use Symfony\Component\HttpFoundation\Response;

class CheckJournalOwnership
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $journalId = $request->route('journal') ?? $request->input('journal_id');
        
        if (!$journalId) {
            return $next($request);
        }
        
        // Get journal
        $journal = $request->route('journal') instanceof Journal 
            ? $request->route('journal') 
            : Journal::find($journalId);
        
        if (!$journal) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Journal not found.',
                    'error' => 'JOURNAL_NOT_FOUND'
                ], 404);
            }
            
            abort(404, 'Journal not found.');
        }
        
        // Check ownership
        if ($journal->user_id !== Auth::id() && !Auth::user()->is_admin) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to access this journal.',
                    'error' => 'JOURNAL_ACCESS_DENIED'
                ], 403);
            }
            
            abort(403, 'You do not have permission to access this journal.');
        }
        
        // Share journal with request for downstream use
        $request->attributes->set('journal', $journal);
        
        return $next($request);
    }
}