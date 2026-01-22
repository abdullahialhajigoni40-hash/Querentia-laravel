<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Journal;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    // Create a new post
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string|min:10|max:5000',
            'type' => 'required|in:journal,question,discussion,announcement,poll',
            'visibility' => 'required|in:public,connections,group,private',
            'journal_id' => 'nullable|exists:journals,id',
            'request_review' => 'boolean',
            'poll_options' => 'nullable|array',
            'poll_options.*' => 'string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if journal belongs to user
        if ($request->journal_id) {
            $journal = Journal::where('id', $request->journal_id)
                ->where('user_id', Auth::id())
                ->first();
                
            if (!$journal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Journal not found or unauthorized.'
                ], 404);
            }
        }

        $post = Post::create([
            'user_id' => Auth::id(),
            'journal_id' => $request->journal_id,
            'type' => $request->type,
            'content' => $request->content,
            'visibility' => $request->visibility,
            'request_review' => $request->request_review ?? false,
            'poll_options' => $request->poll_options ?? null,
        ]);

        // If requesting review, notify relevant users
        if ($request->request_review && $request->journal_id) {
            $this->notifyPotentialReviewers($post);
        }

        // Load relationships for response
        $post->load(['user', 'journal']);

        return response()->json([
            'success' => true,
            'message' => 'Post created successfully.',
            'post' => $post
        ]);
    }

    // Get all posts for feed
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $type = $request->input('type', 'all');
        
        $query = Post::with(['user', 'journal', 'likes', 'comments.user'])
                    ->withCount(['likes', 'comments'])
                    ->visibleTo(Auth::id())
                    ->latest();
        
        if ($type !== 'all') {
            $query->where('type', $type);
        }
        
        $posts = $query->paginate($perPage);
        
        // Mark if user has liked each post
        foreach ($posts as $post) {
            $post->user_has_liked = $post->hasLiked(Auth::id());
        }
        
        return response()->json([
            'success' => true,
            'posts' => $posts
        ]);
    }

    // Like/Unlike a post
    public function like(Post $post)
    {
        $userId = Auth::id();
        
        $like = $post->likes()->where('user_id', $userId)->first();
        
        if ($like) {
            $like->delete();
            $post->decrement('likes_count');
            
            return response()->json([
                'success' => true,
                'action' => 'unliked',
                'likes_count' => $post->fresh()->likes_count
            ]);
        } else {
            Like::create([
                'user_id' => $userId,
                'post_id' => $post->id,
                'type' => 'like'
            ]);
            
            $post->increment('likes_count');
            
            // Create notification for post owner
            if ($post->user_id !== $userId) {
                Notification::create([
                    'user_id' => $post->user_id,
                    'type' => 'post_liked',
                    'data' => json_encode([
                        'post_id' => $post->id,
                        'liker_id' => $userId,
                        'liker_name' => Auth::user()->full_name
                    ]),
                    'read_at' => null
                ]);
            }
            
            return response()->json([
                'success' => true,
                'action' => 'liked',
                'likes_count' => $post->fresh()->likes_count
            ]);
        }
    }

    // Add comment to post
    public function comment(Request $request, Post $post)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string|min:5|max:2000',
            'parent_id' => 'nullable|exists:comments,id',
            'is_review' => 'boolean',
            'rating' => 'nullable|numeric|min:1|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $comment = Comment::create([
            'user_id' => Auth::id(),
            'post_id' => $post->id,
            'parent_id' => $request->parent_id,
            'content' => $request->content,
            'is_review' => $request->is_review ?? false,
            'rating' => $request->rating,
        ]);

        // Update post comment count
        $post->increment('comments_count');
        
        // If it's a review, update review count
        if ($request->is_review) {
            $post->increment('reviews_count');
            
            // Create notification for post owner
            Notification::create([
                'user_id' => $post->user_id,
                'type' => 'post_reviewed',
                'data' => json_encode([
                    'post_id' => $post->id,
                    'reviewer_id' => Auth::id(),
                    'reviewer_name' => Auth::user()->full_name,
                    'rating' => $request->rating
                ]),
                'read_at' => null
            ]);
        }

        // If replying to a comment, increment parent's replies count
        if ($request->parent_id) {
            Comment::where('id', $request->parent_id)->increment('replies_count');
            
            // Notify parent comment owner
            $parentComment = Comment::find($request->parent_id);
            if ($parentComment->user_id !== Auth::id()) {
                Notification::create([
                    'user_id' => $parentComment->user_id,
                    'type' => 'comment_replied',
                    'data' => json_encode([
                        'post_id' => $post->id,
                        'comment_id' => $parentComment->id,
                        'replier_id' => Auth::id(),
                        'replier_name' => Auth::user()->full_name
                    ]),
                    'read_at' => null
                ]);
            }
        }

        $comment->load('user');

        return response()->json([
            'success' => true,
            'message' => 'Comment added successfully.',
            'comment' => $comment
        ]);
    }

    // Get comments for a post
    public function comments(Post $post, Request $request)
    {
        $perPage = $request->input('per_page', 10);
        
        $comments = $post->comments()
            ->with(['user', 'replies.user'])
            ->whereNull('parent_id')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
        
        // Mark if user has liked each comment
        foreach ($comments as $comment) {
            $comment->user_has_liked = $comment->hasLiked(Auth::id());
            foreach ($comment->replies as $reply) {
                $reply->user_has_liked = $reply->hasLiked(Auth::id());
            }
        }
        
        return response()->json([
            'success' => true,
            'comments' => $comments
        ]);
    }

    // Delete post
    public function destroy(Post $post)
    {
        // Check authorization
        if ($post->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.'
            ], 403);
        }

        $post->delete();

        return response()->json([
            'success' => true,
            'message' => 'Post deleted successfully.'
        ]);
    }

    // Notify potential reviewers
    private function notifyPotentialReviewers(Post $post)
    {
        // Get users with matching research interests
        $matchingUsers = User::where('id', '!=', Auth::id())
            ->where(function($query) use ($post) {
                if ($post->journal) {
                    // Match by research interests
                    $interests = Auth::user()->research_interests ?? [];
                    if (!empty($interests)) {
                        foreach ($interests as $interest) {
                            $query->orWhereJsonContains('research_interests', $interest);
                        }
                    }
                    
                    // Match by institution
                    $query->orWhere('institution', Auth::user()->institution);
                }
            })
            ->limit(20) // Limit notifications
            ->get();

        foreach ($matchingUsers as $user) {
            Notification::create([
                'user_id' => $user->id,
                'type' => 'review_requested',
                'data' => json_encode([
                    'post_id' => $post->id,
                    'journal_id' => $post->journal_id,
                    'requester_id' => Auth::id(),
                    'requester_name' => Auth::user()->full_name,
                    'journal_title' => $post->journal->title ?? 'Untitled'
                ]),
                'read_at' => null
            ]);
        }
    }
}