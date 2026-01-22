<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Like;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    // Like/Unlike a comment
    public function like(Comment $comment)
    {
        $userId = Auth::id();
        
        $like = $comment->likes()->where('user_id', $userId)->first();
        
        if ($like) {
            $like->delete();
            
            return response()->json([
                'success' => true,
                'action' => 'unliked',
                'likes_count' => $comment->likes()->count()
            ]);
        } else {
            Like::create([
                'user_id' => $userId,
                'comment_id' => $comment->id,
                'type' => 'like'
            ]);
            
            // Create notification for comment owner
            if ($comment->user_id !== $userId) {
                Notification::create([
                    'user_id' => $comment->user_id,
                    'type' => 'comment_liked',
                    'data' => json_encode([
                        'post_id' => $comment->post_id,
                        'comment_id' => $comment->id,
                        'liker_id' => $userId,
                        'liker_name' => Auth::user()->full_name
                    ]),
                    'read_at' => null
                ]);
            }
            
            return response()->json([
                'success' => true,
                'action' => 'liked',
                'likes_count' => $comment->likes()->count()
            ]);
        }
    }

    // Mark comment as helpful
    public function markHelpful(Comment $comment)
    {
        if ($comment->post->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Only the post owner can mark comments as helpful.'
            ], 403);
        }

        $comment->markAsHelpful();

        // Notify comment owner
        Notification::create([
            'user_id' => $comment->user_id,
            'type' => 'comment_helpful',
            'data' => json_encode([
                'post_id' => $comment->post_id,
                'comment_id' => $comment->id,
                'post_owner_name' => Auth::user()->full_name
            ]),
            'read_at' => null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Comment marked as helpful.',
            'comment' => $comment
        ]);
    }

    // Delete comment
    public function destroy(Comment $comment)
    {
        // Check authorization
        if ($comment->user_id !== Auth::id() && $comment->post->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.'
            ], 403);
        }

        // Update parent comment replies count if applicable
        if ($comment->parent_id) {
            Comment::where('id', $comment->parent_id)->decrement('replies_count');
        }

        // Update post comment count
        $comment->post->decrement('comments_count');
        
        // If it's a review, update review count
        if ($comment->is_review) {
            $comment->post->decrement('reviews_count');
        }

        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Comment deleted successfully.'
        ]);
    }
}