<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\UserConnection;
use App\Models\PeerReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class NotificationsController extends Controller
{
    /**
     * Display the user's notifications
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $type = $request->get('type', 'all');
        
        // Get notifications based on type filter
        $notifications = $this->getNotifications($user, $type);
        
        // Get notification statistics
        $stats = $this->getNotificationStats($user);
        
        return view('notifications', compact('notifications', 'stats', 'type'));
    }
    
    /**
     * Mark notification as read
     */
    public function markAsRead(Notification $notification)
    {
        $user = Auth::user();
        
        // Check if notification belongs to user
        if ($notification->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        $notification->markAsRead();
        
        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
    }
    
    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        
        $user->notifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        
        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read'
        ]);
    }
    
    /**
     * Delete notification
     */
    public function delete(Notification $notification)
    {
        $user = Auth::user();
        
        // Check if notification belongs to user
        if ($notification->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        $notification->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Notification deleted'
        ]);
    }
    
    /**
     * Clear all read notifications
     */
    public function clearRead()
    {
        $user = Auth::user();
        
        $user->notifications()
            ->whereNotNull('read_at')
            ->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Read notifications cleared'
        ]);
    }
    
    /**
     * Get unread notifications count (for API)
     */
    public function unreadCount()
    {
        $user = Auth::user();
        
        $count = $user->notifications()
            ->whereNull('read_at')
            ->count();
        
        return response()->json([
            'success' => true,
            'count' => $count
        ]);
    }
    
    /**
     * Get notifications based on type filter
     */
    private function getNotifications(\App\Models\User $user, $type)
    {
        $query = $user->notifications()
            ->with('user')
            ->orderBy('created_at', 'desc');
        
        switch ($type) {
            case 'unread':
                $query->whereNull('read_at');
                break;
            case 'connection_requests':
                $query->where('type', 'connection_request');
                break;
            case 'reviews':
                $query->whereIn('type', ['review_completed', 'review_requested']);
                break;
            case 'ai_processing':
                $query->where('type', 'ai_processing_complete');
                break;
        }
        
        return $query->paginate(20);
    }
    
    /**
     * Get notification statistics
     */
    private function getNotificationStats(\App\Models\User $user)
    {
        $notifications = $user->notifications();
        
        return [
            'total' => $notifications->count(),
            'unread' => $notifications->whereNull('read_at')->count(),
            'connection_requests' => $notifications->where('type', 'connection_request')->whereNull('read_at')->count(),
            'reviews' => $notifications->whereIn('type', ['review_completed', 'review_requested'])->whereNull('read_at')->count(),
            'ai_processing' => $notifications->where('type', 'ai_processing_complete')->whereNull('read_at')->count(),
        ];
    }
    
    /**
     * Create notification helper methods
     */
    public static function createConnectionRequest($receiverId, $senderId, $connectionId)
    {
        $sender = \App\Models\User::find($senderId);
        
        return Notification::create([
            'user_id' => $receiverId,
            'type' => 'connection_request',
            'title' => 'New Connection Request',
            'message' => "{$sender->full_name} wants to connect with you",
            'data' => [
                'sender_id' => $senderId,
                'connection_id' => $connectionId,
                'sender_name' => $sender->full_name,
            ],
        ]);
    }
    
    public static function createConnectionAccepted($senderId, $receiverId, $connectionId)
    {
        $receiver = \App\Models\User::find($receiverId);
        
        return Notification::create([
            'user_id' => $senderId,
            'type' => 'connection_accepted',
            'title' => 'Connection Accepted',
            'message' => "{$receiver->full_name} accepted your connection request",
            'data' => [
                'receiver_id' => $receiverId,
                'connection_id' => $connectionId,
                'receiver_name' => $receiver->full_name,
            ],
        ]);
    }
    
    public static function createReviewCompleted($authorId, $reviewerId, $journalId, $reviewId, $rating)
    {
        $reviewer = \App\Models\User::find($reviewerId);
        
        return Notification::create([
            'user_id' => $authorId,
            'type' => 'review_completed',
            'title' => 'Review Completed',
            'message' => "{$reviewer->full_name} has completed the review of your paper",
            'data' => [
                'reviewer_id' => $reviewerId,
                'journal_id' => $journalId,
                'review_id' => $reviewId,
                'rating' => $rating,
                'reviewer_name' => $reviewer->full_name,
            ],
        ]);
    }
    
    public static function createReviewRequested($authorId, $reviewerId, $journalId)
    {
        $reviewer = \App\Models\User::find($reviewerId);
        
        return Notification::create([
            'user_id' => $authorId,
            'type' => 'review_requested',
            'title' => 'Review Requested',
            'message' => "{$reviewer->full_name} has requested to review your paper",
            'data' => [
                'reviewer_id' => $reviewerId,
                'journal_id' => $journalId,
                'reviewer_name' => $reviewer->full_name,
            ],
        ]);
    }
    
    public static function createAIProcessingComplete($userId, $journalId)
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => 'ai_processing_complete',
            'title' => 'AI Processing Complete',
            'message' => 'Querentia AI has finished processing your journal draft',
            'data' => [
                'journal_id' => $journalId,
            ],
        ]);
    }
}
