<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'journal_id',
        'reviewer_id',
        'rating',
        'comment',
        'annotations',
        'status',
        'is_helpful',
        'helpful_count',
    ];

    protected $casts = [
        'annotations' => 'array',
        'rating' => 'integer',
    ];

    // Relationship with Journal
    public function journal()
    {
        return $this->belongsTo(Journal::class);
    }

    // Relationship with Reviewer (User)
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    // Scope for helpful reviews
    public function scopeHelpful($query)
    {
        return $query->where('is_helpful', true);
    }

    // Scope for pending reviews
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}