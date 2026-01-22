<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'journal_id',
        'type',
        'content',
        'visibility',
        'request_review',
        'poll_options',
        'likes_count',
        'comments_count',
        'shares_count',
        'reviews_count',
    ];

    protected $casts = [
        'poll_options' => 'array',
        'request_review' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function journal()
    {
        return $this->belongsTo(Journal::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function scopeVisibleTo($query, $userId)
    {
        return $query->where(function($q) use ($userId) {
            $q->where('visibility', 'public')
              ->orWhere('user_id', $userId)
              ->orWhere(function($query) use ($userId) {
                  // Connections only visibility
                  $query->where('visibility', 'connections')
                        ->whereIn('user_id', function($subquery) use ($userId) {
                            $subquery->select('connected_user_id')
                                    ->from('user_connections')
                                    ->where('user_id', $userId)
                                    ->where('status', 'accepted');
                        });
              });
        });
    }

    public function hasLiked($userId)
    {
        return $this->likes()->where('user_id', $userId)->exists();
    }

    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('overall_rating') ?? 0;
    }
}