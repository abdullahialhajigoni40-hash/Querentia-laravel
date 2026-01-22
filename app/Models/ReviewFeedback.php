<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ReviewFeedback extends Model
{
    use HasFactory;

    protected $table = 'review_feedback';

    protected $fillable = [
        'user_id',
        'journal_id',
        'post_id',
        'comment',
        'suggestions',
        'rating',
        'type',
        'status',
        'helpful_count',
        'addressed',
        'addressed_at',
    ];

    protected $casts = [
        'rating' => 'integer',
        'helpful_count' => 'integer',
        'addressed' => 'boolean',
        'addressed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Type constants
    const TYPE_GENERAL = 'general';
    const TYPE_METHODOLOGY = 'methodology';
    const TYPE_RESULTS = 'results';
    const TYPE_ANALYSIS = 'analysis';
    const TYPE_STRUCTURE = 'structure';
    const TYPE_GRAMMAR = 'grammar';
    const TYPE_REFERENCES = 'references';

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    /**
     * Relationship with User (reviewer)
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
     * Relationship with Network Post
     */
    public function post()
    {
        return $this->belongsTo(NetworkPost::class, 'post_id');
    }

    /**
     * Relationship with Helpful votes
     */
    public function helpfulVotes()
    {
        return $this->hasMany(HelpfulVote::class, 'feedback_id');
    }

    /**
     * Check if feedback is approved
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if feedback is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if feedback has been addressed
     */
    public function isAddressed(): bool
    {
        return $this->addressed === true;
    }

    /**
     * Mark feedback as addressed
     */
    public function markAsAddressed(): void
    {
        $this->update([
            'addressed' => true,
            'addressed_at' => now(),
        ]);
    }

    /**
     * Mark feedback as not addressed
     */
    public function markAsNotAddressed(): void
    {
        $this->update([
            'addressed' => false,
            'addressed_at' => null,
        ]);
    }

    /**
     * Approve feedback
     */
    public function approve(): void
    {
        $this->update(['status' => self::STATUS_APPROVED]);
    }

    /**
     * Reject feedback
     */
    public function reject(): void
    {
        $this->update(['status' => self::STATUS_REJECTED]);
    }

    /**
     * Increment helpful count
     */
    public function incrementHelpfulCount(): void
    {
        $this->increment('helpful_count');
    }

    /**
     * Decrement helpful count
     */
    public function decrementHelpfulCount(): void
    {
        $this->decrement('helpful_count');
    }

    /**
     * Check if user can edit this feedback
     */
    public function canEdit(User $user): bool
    {
        return $this->user_id === $user->id || $user->is_admin;
    }

    /**
     * Check if user can delete this feedback
     */
    public function canDelete(User $user): bool
    {
        return $this->user_id === $user->id || 
               $this->journal->user_id === $user->id || 
               $user->is_admin;
    }

    /**
     * Get feedback type label
     */
    public function getTypeLabelAttribute(): string
    {
        $labels = [
            self::TYPE_GENERAL => 'General Feedback',
            self::TYPE_METHODOLOGY => 'Methodology',
            self::TYPE_RESULTS => 'Results',
            self::TYPE_ANALYSIS => 'Analysis',
            self::TYPE_STRUCTURE => 'Structure',
            self::TYPE_GRAMMAR => 'Grammar & Style',
            self::TYPE_REFERENCES => 'References',
        ];

        return $labels[$this->type] ?? ucfirst(str_replace('_', ' ', $this->type));
    }

    /**
     * Get feedback rating stars
     */
    public function getRatingStarsAttribute(): string
    {
        if (!$this->rating) {
            return 'No rating';
        }

        $stars = str_repeat('⭐', $this->rating);
        $emptyStars = str_repeat('☆', 5 - $this->rating);
        
        return $stars . $emptyStars . " ({$this->rating}/5)";
    }

    /**
     * Get feedback age in human readable format
     */
    public function getAgeAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Scope: Approved feedback
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope: Pending feedback
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope: Addressed feedback
     */
    public function scopeAddressed(Builder $query): Builder
    {
        return $query->where('addressed', true);
    }

    /**
     * Scope: Not addressed feedback
     */
    public function scopeNotAddressed(Builder $query): Builder
    {
        return $query->where('addressed', false);
    }

    /**
     * Scope: Helpful feedback (by helpful count)
     */
    public function scopeHelpful(Builder $query, int $threshold = 3): Builder
    {
        return $query->where('helpful_count', '>=', $threshold);
    }

    /**
     * Scope: By type
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: By journal
     */
    public function scopeForJournal(Builder $query, Journal $journal): Builder
    {
        return $query->where('journal_id', $journal->id);
    }

    /**
     * Scope: By user
     */
    public function scopeByUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    /**
     * Get suggestions as array
     */
    public function getSuggestionsArrayAttribute(): array
    {
        if (empty($this->suggestions)) {
            return [];
        }

        if (is_array($this->suggestions)) {
            return $this->suggestions;
        }

        $decoded = json_decode($this->suggestions, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        return array_filter(explode("\n", $this->suggestions));
    }

    /**
     * Calculate feedback quality score
     */
    public function getQualityScoreAttribute(): float
    {
        $score = 0;
        
        // Base score from rating
        if ($this->rating) {
            $score += $this->rating * 2; // 0-10 points
        }
        
        // Points for suggestions
        if (!empty($this->suggestions)) {
            $score += 2;
        }
        
        // Points for being helpful
        if ($this->helpful_count > 0) {
            $score += min($this->helpful_count, 5); // Max 5 points
        }
        
        // Points for detailed comment
        $wordCount = str_word_count($this->comment);
        if ($wordCount > 100) {
            $score += 2;
        } elseif ($wordCount > 50) {
            $score += 1;
        }
        
        return min($score, 20); // Cap at 20 points
    }
}