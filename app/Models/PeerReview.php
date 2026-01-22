<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeerReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'journal_id',
        'reviewer_id',
        'comments',
        'annotations',
        'rating',
        'status',
        'is_anonymous',
        'submitted_at',
        'due_date',
    ];

    protected $casts = [
        'annotations' => 'array',
        'rating' => 'decimal:1',
        'submitted_at' => 'datetime',
        'due_date' => 'datetime',
        'is_anonymous' => 'boolean',
    ];

    public function journal()
    {
        return $this->belongsTo(Journal::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function completeReview($comments, $rating, $annotations = null)
    {
        $this->update([
            'comments' => $comments,
            'rating' => $rating,
            'annotations' => $annotations,
            'status' => 'completed',
            'submitted_at' => now(),
        ]);

        // Update journal's average rating
        $this->journal->updateAverageRating();
    }
}