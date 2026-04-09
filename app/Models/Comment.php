<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Comment
 *
 * @property int $id
 * @property int $user_id
 * @property int $post_id
 * @property int|null $parent_id
 * @property string $content
 * @property bool $is_review
 * @property bool $is_helpful
 * @property int $helpful_count
 * @property int $replies_count
 */
class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'post_id',
        'parent_id',
        'content',
        'is_review',
        'rating',
        'review_criteria',
        'is_helpful',
        'helpful_count',
        'replies_count',
    ];

    protected $casts = [
        'is_review' => 'boolean',
        'is_helpful' => 'boolean',
        'review_criteria' => 'array',
        'rating' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function markAsHelpful()
    {
        $this->update([
            'is_helpful' => true,
            'helpful_count' => $this->helpful_count + 1
        ]);
    }

    public function hasLiked($userId)
    {
        return $this->likes()->where('user_id', $userId)->exists();
    }
}
