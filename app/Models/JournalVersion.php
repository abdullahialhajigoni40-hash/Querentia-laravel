<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class JournalVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'journal_id',
        'content',
        'version_number',
        'ai_provider',
        'is_ai_generated',
        'based_on_feedback',
        'change_summary',
        'word_count',
        'reading_time',
        'created_by',
        'parent_version_id',
    ];

    protected $casts = [
        'version_number' => 'integer',
        'is_ai_generated' => 'boolean',
        'based_on_feedback' => 'array',
        'word_count' => 'integer',
        'reading_time' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship with Journal
     */
    public function journal()
    {
        return $this->belongsTo(Journal::class);
    }

    /**
     * Relationship with User who created the version
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship with parent version
     */
    public function parent()
    {
        return $this->belongsTo(JournalVersion::class, 'parent_version_id');
    }

    /**
     * Relationship with child versions
     */
    public function children()
    {
        return $this->hasMany(JournalVersion::class, 'parent_version_id');
    }

    /**
     * Relationship with Review Feedback that this version is based on
     */
    public function feedbacks()
    {
        if (!$this->based_on_feedback) {
            return collect();
        }
        
        return ReviewFeedback::whereIn('id', $this->based_on_feedback)->get();
    }

    /**
     * Check if this is the first version
     */
    public function isFirstVersion(): bool
    {
        return $this->version_number === 1;
    }

    /**
     * Check if this is the latest version
     */
    public function isLatestVersion(): bool
    {
        $latestVersion = $this->journal->versions()
            ->orderBy('version_number', 'desc')
            ->first();
            
        return $latestVersion && $latestVersion->id === $this->id;
    }

    /**
     * Check if version is AI generated
     */
    public function isAiGenerated(): bool
    {
        return $this->is_ai_generated === true;
    }

    /**
     * Check if version is based on feedback
     */
    public function isBasedOnFeedback(): bool
    {
        return !empty($this->based_on_feedback);
    }

    /**
     * Get version label
     */
    public function getVersionLabelAttribute(): string
    {
        $label = "v{$this->version_number}";
        
        if ($this->isAiGenerated()) {
            $label .= " (AI)";
        }
        
        if ($this->isBasedOnFeedback()) {
            $label .= " [Feedback]";
        }
        
        return $label;
    }

    /**
     * Get version description
     */
    public function getDescriptionAttribute(): string
    {
        $description = "Version {$this->version_number}";
        
        if ($this->isAiGenerated()) {
            $description .= " - AI Generated";
            if ($this->ai_provider) {
                $description .= " using {$this->ai_provider}";
            }
        } else {
            $description .= " - Manual Edit";
        }
        
        if ($this->isBasedOnFeedback()) {
            $description .= " - Based on feedback";
        }
        
        if ($this->change_summary) {
            $description .= " - " . $this->change_summary;
        }
        
        return $description;
    }

    /**
     * Get content preview (first 200 characters)
     */
    public function getPreviewAttribute(): string
    {
        $content = strip_tags($this->content);
        $preview = substr($content, 0, 200);
        
        if (strlen($content) > 200) {
            $preview .= '...';
        }
        
        return $preview;
    }

    /**
     * Get word count (if not stored, calculate it)
     */
    public function getWordCountAttribute(): int
    {
        if ($this->attributes['word_count']) {
            return $this->attributes['word_count'];
        }
        
        return str_word_count(strip_tags($this->content));
    }

    /**
     * Get reading time in minutes
     */
    public function getReadingTimeAttribute(): int
    {
        if ($this->attributes['reading_time']) {
            return $this->attributes['reading_time'];
        }
        
        $wordCount = $this->word_count;
        return max(1, ceil($wordCount / 200)); // 200 words per minute
    }

    /**
     * Calculate differences from previous version
     */
    public function getDiffFromPrevious(): array
    {
        $previous = $this->journal->versions()
            ->where('version_number', '<', $this->version_number)
            ->orderBy('version_number', 'desc')
            ->first();
            
        if (!$previous) {
            return [
                'added' => $this->content,
                'removed' => '',
                'similarity' => 0,
            ];
        }
        
        $current = $this->content;
        $previousContent = $previous->content;
        
        // Simple similarity calculation (for demonstration)
        similar_text($current, $previousContent, $similarity);
        
        return [
            'added' => $this->calculateAddedText($previousContent, $current),
            'removed' => $this->calculateRemovedText($previousContent, $current),
            'similarity' => round($similarity, 2),
        ];
    }

    /**
     * Calculate added text compared to previous version
     */
    private function calculateAddedText(string $oldText, string $newText): string
    {
        // Simple implementation - in real app you'd use a proper diff algorithm
        $oldWords = str_word_count($oldText, 1);
        $newWords = str_word_count($newText, 1);
        
        $addedWords = array_diff($newWords, $oldWords);
        
        return implode(' ', $addedWords);
    }

    /**
     * Calculate removed text compared to previous version
     */
    private function calculateRemovedText(string $oldText, string $newText): string
    {
        // Simple implementation
        $oldWords = str_word_count($oldText, 1);
        $newWords = str_word_count($newText, 1);
        
        $removedWords = array_diff($oldWords, $newWords);
        
        return implode(' ', $removedWords);
    }

    /**
     * Get feedback IDs as array
     */
    public function getFeedbackIdsAttribute(): array
    {
        if (empty($this->based_on_feedback)) {
            return [];
        }
        
        if (is_array($this->based_on_feedback)) {
            return $this->based_on_feedback;
        }
        
        return json_decode($this->based_on_feedback, true) ?? [];
    }

    /**
     * Scope: AI generated versions
     */
    public function scopeAiGenerated(Builder $query): Builder
    {
        return $query->where('is_ai_generated', true);
    }

    /**
     * Scope: Manual versions
     */
    public function scopeManual(Builder $query): Builder
    {
        return $query->where('is_ai_generated', false);
    }

    /**
     * Scope: Versions based on feedback
     */
    public function scopeBasedOnFeedback(Builder $query): Builder
    {
        return $query->whereNotNull('based_on_feedback')
                     ->where('based_on_feedback', '!=', '[]');
    }

    /**
     * Scope: By version number range
     */
    public function scopeVersionRange(Builder $query, int $from, int $to): Builder
    {
        return $query->whereBetween('version_number', [$from, $to]);
    }

    /**
     * Scope: Latest versions only
     */
    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderBy('version_number', 'desc')->limit(1);
    }

    /**
     * Create a new version from current journal content
     */
    public static function createFromJournal(Journal $journal, array $data = []): JournalVersion
    {
        $latestVersion = $journal->versions()->latest()->first();
        $versionNumber = $latestVersion ? $latestVersion->version_number + 1 : 1;
        
        return self::create(array_merge([
            'journal_id' => $journal->id,
            'content' => $journal->ai_generated_content ?? $journal->getFullContent(),
            'version_number' => $versionNumber,
            'ai_provider' => $journal->ai_provider_used,
            'is_ai_generated' => !empty($journal->ai_generated_content),
            'word_count' => $journal->word_count,
            'reading_time' => $journal->reading_time,
            'created_by' => auth()->id() ?? $journal->user_id,
        ], $data));
    }

    /**
     * Restore this version to the journal
     */
    public function restoreToJournal(): void
    {
        $this->journal->update([
            'ai_generated_content' => $this->content,
            'current_version_id' => $this->id,
            'ai_provider_used' => $this->ai_provider,
        ]);
        
        // Log the restoration
        \App\Models\JournalAuditLog::create([
            'journal_id' => $this->journal->id,
            'user_id' => auth()->id(),
            'action' => 'version_restored',
            'details' => [
                'from_version' => $this->journal->current_version_id,
                'to_version' => $this->id,
                'version_number' => $this->version_number,
            ],
        ]);
    }
}