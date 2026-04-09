<?php

namespace App\Http\Controllers;

use App\Models\PeerReview;
use App\Models\Journal;
use App\Models\User;
use App\Services\JournalRenderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MyReviewsController extends Controller
{
    /**
     * Display the user's reviews dashboard
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get review statistics
        $stats = $this->getReviewStats($user);
        
        // Get pending reviews (journals assigned to user for review)
        $pendingReviews = $this->getPendingReviews($user);
        
        // Get completed reviews
        $completedReviews = $this->getCompletedReviews($user);
        
        // Get in-progress reviews
        $inProgressReviews = $this->getInProgressReviews($user);
        
        return view('network.my-reviews', compact(
            'stats',
            'pendingReviews',
            'completedReviews',
            'inProgressReviews'
        ));
    }
    
    /**
     * Show the review interface for a specific journal
     */
    public function review(Journal $journal)
    {
        $user = Auth::user();
        
        // Check if user is assigned to review this journal
        $peerReview = PeerReview::where('journal_id', $journal->id)
            ->where('reviewer_id', $user->id)
            ->first();
            
        if (!$peerReview) {
            return redirect()->route('my-reviews')
                ->with('error', 'You are not assigned to review this paper.');
        }

        $renderer = app(JournalRenderService::class);
        $paperHtml = $renderer->buildFinalJournalHtml($journal, true);
        
        return view('network.review-interface', compact('journal', 'peerReview', 'paperHtml'));
    }
    
    /**
     * Submit a review
     */
    public function submitReview(Request $request, Journal $journal)
    {
        $request->validate([
            'rating' => 'required|numeric|min:1|max:5',
            'comments' => 'required|string|min:50',
            'annotations' => 'nullable|array',
            'is_anonymous' => 'boolean'
        ]);
        
        $user = Auth::user();
        
        // Find the peer review assignment
        $peerReview = PeerReview::where('journal_id', $journal->id)
            ->where('reviewer_id', $user->id)
            ->first();
            
        if (!$peerReview) {
            return response()->json([
                'success' => false,
                'message' => 'Review assignment not found'
            ], 404);
        }
        
        try {
            DB::beginTransaction();
            
            // Complete the review
            $peerReview->completeReview(
                $request->comments,
                $request->rating,
                $request->annotations
            );
            
            // Update anonymous preference
            if ($request->has('is_anonymous')) {
                $peerReview->update(['is_anonymous' => $request->is_anonymous]);
            }
            
            // Create notification for journal author
            $this->notifyAuthorOfReview($journal, $user, $peerReview);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Review submitted successfully',
                'redirect' => route('my-reviews')
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit review: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Save review as draft (in progress)
     */
    public function saveDraft(Request $request, Journal $journal)
    {
        $request->validate([
            'rating' => 'nullable|numeric|min:1|max:5',
            'comments' => 'nullable|string',
            'annotations' => 'nullable|array'
        ]);
        
        $user = Auth::user();
        
        $peerReview = PeerReview::where('journal_id', $journal->id)
            ->where('reviewer_id', $user->id)
            ->first();
            
        if (!$peerReview) {
            return response()->json([
                'success' => false,
                'message' => 'Review assignment not found'
            ], 404);
        }
        
        $peerReview->update([
            'status' => 'in_progress',
            'comments' => $request->comments,
            'rating' => $request->rating,
            'annotations' => $request->annotations,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Draft saved successfully'
        ]);
    }
    
    /**
     * Get available papers for review
     */
    public function findPapers()
    {
        $user = Auth::user();
        
        // Get journals that need reviewers in user's field
        $availableJournals = Journal::where('status', 'pending_review')
            ->where('user_id', '!=', $user->id)
            ->whereDoesntHave('peerReviews', function($query) use ($user) {
                $query->where('reviewer_id', $user->id);
            })
            ->where(function($query) use ($user) {
                // Match by area of study or related fields
                $query->where('area_of_study', $user->area_of_study)
                      ->orWhereIn('area_of_study', $this->getRelatedFields($user->area_of_study));
            })
            ->with('user')
            ->paginate(10);
            
        return view('network.find-papers', compact('availableJournals'));
    }
    
    /**
     * Request to review a paper
     */
    public function requestReview(Journal $journal)
    {
        $user = Auth::user();
        
        // Check if already assigned
        $existingReview = PeerReview::where('journal_id', $journal->id)
            ->where('reviewer_id', $user->id)
            ->first();
            
        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'You are already assigned to review this paper'
            ], 400);
        }
        
        // Create peer review assignment
        $peerReview = PeerReview::create([
            'journal_id' => $journal->id,
            'reviewer_id' => $user->id,
            'status' => 'pending',
            'due_date' => now()->addDays(14), // 2 weeks deadline
        ]);
        
        // Notify journal author
        $this->notifyAuthorOfReviewRequest($journal, $user);
        
        return response()->json([
            'success' => true,
            'message' => 'Review request submitted successfully',
            'redirect' => route('my-reviews')
        ]);
    }
    
    /**
     * Get review statistics for the user
     */
    private function getReviewStats(User $user)
    {
        $reviews = PeerReview::where('reviewer_id', $user->id);
        
        return [
            'pending' => $reviews->where('status', 'pending')->count(),
            'in_progress' => $reviews->where('status', 'in_progress')->count(),
            'completed' => $reviews->where('status', 'completed')->count(),
            'average_rating' => $reviews->where('status', 'completed')->avg('rating') ?? 0,
            'total_reviews' => $reviews->count(),
        ];
    }
    
    /**
     * Get pending reviews for the user
     */
    private function getPendingReviews(User $user)
    {
        return PeerReview::where('reviewer_id', $user->id)
            ->where('status', 'pending')
            ->with(['journal.user'])
            ->orderBy('due_date', 'asc')
            ->get();
    }
    
    /**
     * Get completed reviews for the user
     */
    private function getCompletedReviews(User $user)
    {
        return PeerReview::where('reviewer_id', $user->id)
            ->where('status', 'completed')
            ->with(['journal.user'])
            ->orderBy('submitted_at', 'desc')
            ->paginate(10);
    }
    
    /**
     * Get in-progress reviews for the user
     */
    private function getInProgressReviews(User $user)
    {
        return PeerReview::where('reviewer_id', $user->id)
            ->where('status', 'in_progress')
            ->with(['journal.user'])
            ->orderBy('updated_at', 'desc')
            ->get();
    }
    
    /**
     * Get related fields based on user's area of study
     */
    private function getRelatedFields($areaOfStudy)
    {
        $relatedFields = [
            'Computer Science' => ['Artificial Intelligence', 'Machine Learning', 'Data Science', 'Software Engineering'],
            'Medicine' => ['Healthcare', 'Medical Science', 'Pharmacology', 'Biology'],
            'Physics' => ['Mathematics', 'Engineering', 'Astronomy', 'Chemistry'],
            'Biology' => ['Medicine', 'Genetics', 'Ecology', 'Biochemistry'],
            'Chemistry' => ['Physics', 'Biology', 'Materials Science', 'Pharmacology'],
        ];
        
        return $relatedFields[$areaOfStudy] ?? [];
    }
    
    /**
     * Notify journal author of completed review
     */
    private function notifyAuthorOfReview(Journal $journal, User $reviewer, PeerReview $peerReview)
    {
        // Create notification for journal author
        $journal->user->notifications()->create([
            'type' => 'review_completed',
            'title' => 'Review Completed',
            'message' => "{$reviewer->full_name} has completed the review of your paper '{$journal->title}'",
            'data' => [
                'journal_id' => $journal->id,
                'reviewer_id' => $reviewer->id,
                'review_id' => $peerReview->id,
                'rating' => $peerReview->rating,
            ],
        ]);
    }
    
    /**
     * Notify journal author of review request
     */
    private function notifyAuthorOfReviewRequest(Journal $journal, User $reviewer)
    {
        // Create notification for journal author
        $journal->user->notifications()->create([
            'type' => 'review_requested',
            'title' => 'Review Requested',
            'message' => "{$reviewer->full_name} has requested to review your paper '{$journal->title}'",
            'data' => [
                'journal_id' => $journal->id,
                'reviewer_id' => $reviewer->id,
            ],
        ]);
    }
}
