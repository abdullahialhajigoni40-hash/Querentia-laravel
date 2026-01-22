<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AIUsageLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'journal_id',
        'provider',
        'tokens_used',
        'estimated_cost',
        'task_type',
        'request_data',
        'response_data',
        'ip_address',
        'response_time',
        'success',
        'error_message',
    ];

    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array',
        'estimated_cost' => 'decimal:6',
        'response_time' => 'decimal:3',
        'success' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Task type constants
    const TASK_JOURNAL_GENERATION = 'journal_generation';
    const TASK_SECTION_ENHANCEMENT = 'section_enhancement';
    const TASK_GRAMMAR_CHECK = 'grammar_check';
    const TASK_SUMMARIZATION = 'summarization';
    const TASK_REFERENCE_SUGGESTION = 'reference_suggestion';
    const TASK_JOURNAL_IMPROVEMENT = 'journal_improvement';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function journal()
    {
        return $this->belongsTo(Journal::class);
    }

    /**
     * Get task type label
     */
    public function getTaskTypeLabelAttribute(): string
    {
        $labels = [
            self::TASK_JOURNAL_GENERATION => 'Journal Generation',
            self::TASK_SECTION_ENHANCEMENT => 'Section Enhancement',
            self::TASK_GRAMMAR_CHECK => 'Grammar Check',
            self::TASK_SUMMARIZATION => 'Summarization',
            self::TASK_REFERENCE_SUGGESTION => 'Reference Suggestion',
            self::TASK_JOURNAL_IMPROVEMENT => 'Journal Improvement',
        ];

        return $labels[$this->task_type] ?? ucfirst(str_replace('_', ' ', $this->task_type));
    }

    /**
     * Check if the request was successful
     */
    public function wasSuccessful(): bool
    {
        return $this->success === true;
    }

    /**
     * Get formatted cost
     */
    public function getFormattedCostAttribute(): string
    {
        return '$' . number_format($this->estimated_cost, 6);
    }

    /**
     * Get response time in milliseconds
     */
    public function getResponseTimeMsAttribute(): float
    {
        return $this->response_time * 1000;
    }

    /**
     * Scope: Successful requests
     */
    public function scopeSuccessful($query)
    {
        return $query->where('success', true);
    }

    /**
     * Scope: Failed requests
     */
    public function scopeFailed($query)
    {
        return $query->where('success', false);
    }

    /**
     * Scope: By provider
     */
    public function scopeByProvider($query, $provider)
    {
        return $query->where('provider', $provider);
    }

    /**
     * Scope: By task type
     */
    public function scopeByTaskType($query, $taskType)
    {
        return $query->where('task_type', $taskType);
    }

    /**
     * Scope: By user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Recent logs (last 30 days)
     */
    public function scopeRecent($query)
    {
        return $query->where('created_at', '>=', now()->subDays(30));
    }

    /**
     * Get request data as pretty JSON
     */
    public function getRequestDataPrettyAttribute(): string
    {
        return json_encode($this->request_data, JSON_PRETTY_PRINT);
    }

    /**
     * Get response data as pretty JSON
     */
    public function getResponseDataPrettyAttribute(): string
    {
        return json_encode($this->response_data, JSON_PRETTY_PRINT);
    }
}