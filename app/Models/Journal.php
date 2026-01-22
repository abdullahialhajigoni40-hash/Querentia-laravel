<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'abstract',
        'authors',
        'introduction',
        'area_of_study',
        'additional_notes',
        'materials_methods',
        'results_discussion',
        'conclusion',
        'references',
        'annexes',
        'maps_figures',
        'original_sections',
        'raw_content',
        'ai_generated_content',
        'status',
        'journal_name',
        'journal_template',
        'ai_provider_used',
        'ai_usage_count',
        'published_at',
        'completed_at',
        'posted_for_review_at',
    ];
    
    protected $casts = [
        'authors' => 'array',
        'references' => 'array',
        'annexes' => 'array',
        'maps_figures' => 'array',
        'original_sections' => 'array',
        'raw_content' => 'array',
        'published_at' => 'datetime',
        'completed_at' => 'datetime',
        'posted_for_review_at' => 'datetime',
    ];
    
    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_AI_PROCESSING = 'ai_processing';
    const STATUS_UNDER_REVIEW = 'under_review';
    const STATUS_REVISED = 'revised';
    const STATUS_PUBLISHED = 'published';
    const STATUS_AI_FAILED = 'ai_failed';
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function versions()
    {
        return $this->hasMany(JournalVersion::class);
    }
    
    public function currentVersion()
    {
        return $this->belongsTo(JournalVersion::class, 'current_version_id');
    }
    
    public function posts()
    {
        return $this->hasMany(NetworkPost::class);
    }
    
    public function reviews()
    {
        return $this->hasMany(ReviewFeedback::class);
    }


    /**
 * Check if journal is under review
 */
public function getIsUnderReviewAttribute(): bool
{
    return $this->status === self::STATUS_UNDER_REVIEW;
}

/**
 * Check if journal is ready for publication
 */
public function getIsReadyForPublicationAttribute(): bool
{
    return $this->status === self::STATUS_UNDER_REVIEW && 
           $this->reviews()->where('rating', '>=', 4)->count() >= 2;
}

/**
 * Get the review deadline (7 days from posting)
 */
public function getReviewDeadlineAttribute(): ?\Carbon\Carbon
{
    if (!$this->posted_for_review_at) {
        return null;
    }
    
    return $this->posted_for_review_at->addDays(7);
}


    /**
 * Check if journal has human-written content
 */
public function hasHumanContent(): bool
{
    $fields = [
        'abstract', 'introduction', 'area_of_study', 
        'additional_notes', 'methodology', 'results_discussion', 
        'conclusion', 'references'
    ];
    
    foreach ($fields as $field) {
        if (!empty($this->$field)) {
            return true;
        }
    }
    
    return false;
}

/**
 * Format AI content for display
 */
public function formatAIContent(?string $content): string
{
    if (!$content) {
        return '<div class="text-center py-8 text-gray-400">
                    <i class="fas fa-robot text-3xl mb-3"></i>
                    <p>No AI-generated content available.</p>
                </div>';
    }
    
    // Convert markdown-like formatting to HTML
    $formatted = htmlspecialchars($content);
    
    // Convert newlines to paragraphs
    $formatted = '<p>' . str_replace("\n\n", '</p><p>', $formatted) . '</p>';
    
    // Convert single newlines to line breaks
    $formatted = str_replace("\n", '<br>', $formatted);
    
    // Format section headers
    $formatted = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $formatted);
    $formatted = preg_replace('/\#\s(.*?)(?:\n|$)/', '<h3 class="section-title mt-6 mb-3">$1</h3>', $formatted);
    $formatted = preg_replace('/\#\#\s(.*?)(?:\n|$)/', '<h4 class="font-bold text-lg mt-4 mb-2">$1</h4>', $formatted);
    
    return $formatted;
}
    
    /**
     * Get word count of AI generated content
     */
    public function getWordCountAttribute()
    {
        if (!$this->ai_generated_content) {
            return 0;
        }
        
        return str_word_count(strip_tags($this->ai_generated_content));
    }
    
    /**
     * Get reading time in minutes
     */
    public function getReadingTimeAttribute()
    {
        $words = $this->word_count;
        return ceil($words / 200); // 200 words per minute
    }
    
    /**
     * Check if journal is being processed by AI
     */
    public function getIsProcessingAttribute()
    {
        return $this->status === self::STATUS_AI_PROCESSING;
    }
    
    /**
     * Check if journal is ready for review
     */
    public function getIsReadyForReviewAttribute()
    {
        return $this->status === self::STATUS_DRAFT && !empty($this->ai_generated_content);
    }
    
    /**
     * Get average rating from reviews
     */
    public function getAverageRatingAttribute()
    {
        if ($this->reviews->isEmpty()) {
            return null;
        }
        
        return $this->reviews->avg('rating');
    }
}