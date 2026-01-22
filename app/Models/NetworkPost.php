<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class NetworkPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'journal_id',
        'content',
        'type',
        'visibility',
        'request_feedback_types',
        'status',
        'published_at',
        'like_count',
        'comment_count',
        'share_count',
    ];

    protected $casts = [
        'request_feedback_types' => 'array',
        'published_at' => 'datetime',
        'like_count' => 'integer',
        'comment_count' => 'integer',
        'share_count' => 'integer',
    ];

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';
    const STATUS_ARCHIVED = 'archived';
    const STATUS_HIDDEN = 'hidden';

    // Type constants
    const TYPE_JOURNAL = 'journal';
    const TYPE_QUESTION = 'question';
    const TYPE_ANNOUNCEMENT = 'announcement';
    const TYPE_DISCUSSION = 'discussion';

    // Visibility constants
    const VISIBILITY_PUBLIC = 'public';
    const VISIBILITY_CONNECTIONS = 'connections';
    const VISIBILITY_PRIVATE = 'private';
    const VISIBILITY_EXPERTS = 'experts';

    /**
     * Relationship with User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship with Journal
     */
    public function journal()
    {
        return $this->belongsTo(Journal::class);
    }

    /**
     * Relationship with Comments
     */
    public function comments()
    {
        return $this->hasMany(Comment::class, 'post_id');
    }

    /**
     * Relationship with Likes
     */
    public function likes()
    {
        return $this->hasMany(Like::class, 'post_id');
    }

    /**
     * Relationship with Review Feedback
     */
    public function reviewFeedbacks()
    {
        return $this->hasMany(ReviewFeedback::class, 'post_id');
    }

    /**
     * Check if post is published
     */
    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    /**
     * Check if post is requesting feedback
     */
    public function isRequestingFeedback(): bool
    {
        return !empty($this->request_feedback_types) && 
               $this->type === self::TYPE_JOURNAL;
    }

    /**
     * Get feedback types as array
     */
    public function getFeedbackTypesAttribute(): array
    {
        if (empty($this->request_feedback_types)) {
            return ['general'];
        }
        
        return is_array($this->request_feedback_types) 
            ? $this->request_feedback_types 
            : json_decode($this->request_feedback_types, true) ?? ['general'];
    }

    /**
     * Check if user can view this post
     */
    public function canView(User $user): bool
    {
        // Owner can always view
        if ($this->user_id === $user->id) {
            return true;
        }

        // Check visibility
        switch ($this->visibility) {
            case self::VISIBILITY_PUBLIC:
                return true;
                
            case self::VISIBILITY_CONNECTIONS:
                return $user->isConnectedTo($this->user_id);
                
            case self::VISIBILITY_EXPERTS:
                return $user->isExpertInField($this->journal->area_of_study ?? 'general');
                
            case self::VISIBILITY_PRIVATE:
                return false;
                
            default:
                return false;
        }
    }

    /**
     * Check if user can comment on this post
     */
    public function canComment(User $user): bool
    {
        if (!$this->canView($user)) {
            return false;
        }

        // Post must be published
        if (!$this->isPublished()) {
            return false;
        }

        return true;
    }

    /**
     * Check if user can review this journal post
     */
    public function canReview(User $user): bool
    {
        if (!$this->canView($user) || $this->user_id === $user->id) {
            return false;
        }

        if (!$this->journal || !$this->isRequestingFeedback()) {
            return false;
        }

        return true;
    }

    /**
     * Scope: Published posts
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    /**
     * Scope: Posts visible to user
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query->where(function ($q) use ($user) {
            // Public posts
            $q->where('visibility', self::VISIBILITY_PUBLIC)
              ->orWhere('user_id', $user->id)
              ->orWhere(function ($q2) use ($user) {
                  // Posts from connections
                  $connectedUserIds = $user->connections()
                      ->where('status', 'accepted')
                      ->pluck('connected_user_id')
                      ->toArray();
                  
                  $q2->where('visibility', self::VISIBILITY_CONNECTIONS)
                     ->whereIn('user_id', $connectedUserIds);
              });
        });
    }

    /**
     * Scope: Journal posts
     */
    public function scopeJournalPosts(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_JOURNAL);
    }

    /**
     * Scope: Posts requesting feedback
     */
    public function scopeRequestingFeedback(Builder $query): Builder
    {
        return $query->whereNotNull('request_feedback_types')
                     ->where('type', self::TYPE_JOURNAL);
    }

    /**
     * Increment like count
     */
    public function incrementLikeCount(): void
    {
        $this->increment('like_count');
    }

    /**
     * Decrement like count
     */
    public function decrementLikeCount(): void
    {
        $this->decrement('like_count');
    }

    /**
     * Increment comment count
     */
    public function incrementCommentCount(): void
    {
        $this->increment('comment_count');
    }

    /**
     * Decrement comment count
     */
    public function decrementCommentCount(): void
    {
        $this->decrement('comment_count');
    }

    /**
     * Get post excerpt
     */
    public function getExcerptAttribute(): string
    {
        $content = strip_tags($this->content);
        $excerpt = substr($content, 0, 150);
        
        if (strlen($content) > 150) {
            $excerpt .= '...';
        }
        
        return $excerpt;
    }

    /**
     * Get post reading time in minutes
     */
    public function getReadingTimeAttribute(): int
    {
        $wordCount = str_word_count(strip_tags($this->content));
        return max(1, ceil($wordCount / 200)); // 200 words per minute
    }

    /**
     * Get post age in human readable format
     */
    public function getAgeAttribute(): string
    {
        return $this->published_at->diffForHumans();
    }
}