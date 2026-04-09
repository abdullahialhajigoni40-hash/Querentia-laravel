<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ReviewFeedback;
use App\Models\Journal;

class FeedbackController extends Controller
{
    /**
     * Update feedback
     */
    public function update(Request $request, ReviewFeedback $feedback)
    {
        // Authorization check would go here
        $feedback->update($request->validate([
            'content' => 'required|string',
            'type' => 'required|string|in:general,methodology,results,ai_content',
        ]));
        
        return response()->json(['success' => true]);
    }
    
    /**
     * Delete feedback
     */
    public function destroy(ReviewFeedback $feedback)
    {
        // Authorization check would go here
        $feedback->delete();
        
        return response()->json(['success' => true]);
    }
}
