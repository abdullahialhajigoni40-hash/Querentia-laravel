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
        'methodology',
        'methodology_blocks',
        'materials_methods',
        'results_discussion',
        'conclusion',
        'references',
        'annexes',
        'maps_figures',
        'original_sections',
        'raw_content',
        'ai_generated_content',
        'ai_edited_content',
        'source_document_disk',
        'source_document_path',
        'source_document_original_name',
        'source_document_mime',
        'source_document_size',
        'source_extracted_text',
        'source_word_count',
        'source_page_count',
        'ingestion_status',
        'ingestion_progress',
        'ingestion_error',
        'status',
        'is_ai_journal',
        'journal_name',
        'journal_template',
        'license',
        'ai_provider_used',
        'ai_usage_count',
        'published_at',
        'completed_at',
        'posted_for_review_at',
        'average_rating',
    ];
    
    protected $casts = [
        'authors' => 'array',
        'references' => 'array',
        'annexes' => 'array',
        'maps_figures' => 'array',
        'original_sections' => 'array',
        'raw_content' => 'array',
        'methodology_blocks' => 'array',
        'source_word_count' => 'integer',
        'source_page_count' => 'integer',
        'ingestion_progress' => 'integer',
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

    public function images()
    {
        return $this->hasMany(JournalImage::class);
    }

    public function figures()
    {
        return $this->hasMany(JournalImage::class)->where('kind', 'figure')->orderBy('sort_order')->orderBy('id');
    }

    public function peerReviews()
    {
        return $this->hasMany(PeerReview::class);
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
        $src = $this->ai_edited_content ?: $this->ai_generated_content;
        if (!$src) {
            return 0;
        }
        
        return str_word_count(strip_tags($src));
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
    
    /**
     * Update average rating from peer reviews
     */
    public function updateAverageRating()
    {
        $completedReviews = $this->peerReviews()->where('status', 'completed');
        
        if ($completedReviews->count() > 0) {
            $this->average_rating = $completedReviews->avg('rating');
            $this->save();
        }
    }
}