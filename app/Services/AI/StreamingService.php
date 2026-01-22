<?php

namespace App\Services\AI;

use App\Models\User;
use App\Models\Journal;
use App\Models\AIUsageLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Exception;

class StreamingService
{
    protected $aiJournalService;
    
    public function __construct(AIJournalService $aiJournalService)
    {
        $this->aiJournalService = $aiJournalService;
    }
    
    /**
     * Start journal generation stream
     */
    public function startJournalStream(User $user, array $sections, $journalId = null, $provider = 'deepseek')
    {
        try {
            // Validate provider
            if (!$this->aiJournalService->isProviderAvailable($provider)) {
                throw new Exception("AI provider '{$provider}' is not available");
            }
            
            // Check rate limits
            if (!$this->checkRateLimit($user)) {
                throw new Exception('Rate limit exceeded. Please try again later.');
            }
            
            // Check subscription
            if (!$this->checkSubscription($user)) {
                throw new Exception('Subscription limit reached. Please upgrade your plan.');
            }
            
            // Get or create journal
            $journal = $this->getOrCreateJournal($user, $journalId, $sections);
            
            // Create usage log
            $usageLog = $this->createUsageLog($user, $journal, $provider, 'journal_generation');
            
            return [
                'success' => true,
                'journal' => $journal,
                'usage_log' => $usageLog,
                'stream_id' => $this->generateStreamId($user, $journal)
            ];
            
        } catch (Exception $e) {
            Log::error('Streaming Service Error - Start Journal', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Start section enhancement stream
     */
    public function startSectionStream(User $user, string $content, string $type, $provider = 'deepseek')
    {
        try {
            // Validate provider
            if (!$this->aiJournalService->isProviderAvailable($provider)) {
                throw new Exception("AI provider '{$provider}' is not available");
            }
            
            // Check rate limits
            if (!$this->checkRateLimit($user)) {
                throw new Exception('Rate limit exceeded. Please try again later.');
            }
            
            // Check subscription
            if (!$this->checkSubscription($user)) {
                throw new Exception('Subscription limit reached. Please upgrade your plan.');
            }
            
            // Create usage log
            $usageLog = $this->createUsageLog($user, null, $provider, 'section_enhancement', $type);
            
            return [
                'success' => true,
                'usage_log' => $usageLog,
                'stream_id' => $this->generateStreamId($user, null, 'section')
            ];
            
        } catch (Exception $e) {
            Log::error('Streaming Service Error - Start Section', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Stream AI response chunk by chunk
     */
    public function streamResponse($provider, $prompt, callable $chunkCallback, array $options = [])
    {
        try {
            $aiService = $this->aiJournalService->getProviderService($provider);
            
            $streamOptions = array_merge([
                'max_tokens' => 4000,
                'temperature' => 0.7,
                'stream' => true,
            ], $options);
            
            return $aiService->streamResponse($prompt, $chunkCallback, $streamOptions);
            
        } catch (Exception $e) {
            Log::error('Streaming Service Error - Stream Response', [
                'provider' => $provider,
                'error' => $e->getMessage()
            ]);
            
            throw new Exception("Failed to stream AI response: " . $e->getMessage());
        }
    }
    
    /**
     * Complete stream and update records
     */
    public function completeStream($usageLogId, $data = [])
    {
        try {
            $usageLog = AIUsageLog::findOrFail($usageLogId);
            
            $usageLog->update([
                'status' => 'completed',
                'tokens_used' => $data['tokens_used'] ?? 0,
                'cost' => $data['cost'] ?? 0,
                'response_data' => $data['response_data'] ?? null,
                'completed_at' => now(),
            ]);
            
            // Update user's AI usage
            if ($usageLog->user) {
                $usageLog->user->increment('ai_usage_count');
                
                if ($usageLog->user->ai_usage_tokens) {
                    $usageLog->user->increment('ai_usage_tokens', $usageLog->tokens_used);
                } else {
                    $usageLog->user->ai_usage_tokens = $usageLog->tokens_used;
                    $usageLog->user->save();
                }
            }
            
            // Update journal if exists
            if ($usageLog->journal_id && $usageLog->journal) {
                $journal = $usageLog->journal;
                
                $updates = [
                    'ai_generated_at' => now(),
                    'ai_provider' => $usageLog->provider,
                    'status' => 'ai_draft',
                ];
                
                if (isset($data['content'])) {
                    $updates['content'] = $data['content'];
                }
                
                if (isset($data['abstract'])) {
                    $updates['abstract'] = $data['abstract'];
                }
                
                $journal->update($updates);
            }
            
            return [
                'success' => true,
                'usage_log' => $usageLog,
                'journal' => $usageLog->journal ?? null
            ];
            
        } catch (Exception $e) {
            Log::error('Streaming Service Error - Complete Stream', [
                'usage_log_id' => $usageLogId,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Fail stream and update records
     */
    public function failStream($usageLogId, $error)
    {
        try {
            $usageLog = AIUsageLog::findOrFail($usageLogId);
            
            $usageLog->update([
                'status' => 'failed',
                'error_message' => $error,
                'completed_at' => now(),
            ]);
            
            return [
                'success' => false,
                'error' => $error,
                'usage_log' => $usageLog
            ];
            
        } catch (Exception $e) {
            Log::error('Streaming Service Error - Fail Stream', [
                'usage_log_id' => $usageLogId,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Check rate limit for user
     */
    public function checkRateLimit(User $user): bool
    {
        $rateLimit = config('ai.rate_limit', 100);
        $rateLimitPeriod = config('ai.rate_limit_period', 60); // minutes
        
        $recentUsage = AIUsageLog::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subMinutes($rateLimitPeriod))
            ->where('status', 'completed')
            ->count();
        
        return $recentUsage < $rateLimit;
    }
    
    /**
     * Check subscription limits
     */
    public function checkSubscription(User $user): bool
    {
        $requiresSubscription = config('ai.requires_subscription', false);
        
        if (!$requiresSubscription) {
            return true;
        }
        
        // Check if user has active subscription
        if ($user->hasActiveSubscription()) {
            return true;
        }
        
        // Check free tier limit
        $freeTierLimit = config('ai.free_tier_limit', 10);
        $usageCount = $user->ai_usage_count ?? 0;
        
        return $usageCount < $freeTierLimit;
    }
    
    /**
     * Get or create journal
     */
    protected function getOrCreateJournal(User $user, $journalId, array $sections)
    {
        if ($journalId) {
            return Journal::where('id', $journalId)
                ->where('user_id', $user->id)
                ->firstOrFail();
        }
        
        return Journal::create([
            'user_id' => $user->id,
            'title' => $sections['title'] ?? 'AI Generated Journal - ' . Carbon::now()->format('Y-m-d H:i:s'),
            'abstract' => $sections['abstract'] ?? null,
            'area_of_study' => $sections['area_of_study'] ?? 'General',
            'status' => 'ai_draft',
            'ai_percentage' => 100,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    
    /**
     * Create usage log
     */
    protected function createUsageLog(User $user, $journal = null, $provider, $action, $actionType = null)
    {
        return AIUsageLog::create([
            'user_id' => $user->id,
            'journal_id' => $journal ? $journal->id : null,
            'provider' => $provider,
            'action' => $action,
            'action_type' => $actionType,
            'tokens_used' => 0,
            'cost' => 0,
            'status' => 'processing',
            'request_data' => [],
            'response_data' => null,
            'error_message' => null,
            'created_at' => now(),
            'started_at' => now(),
        ]);
    }
    
    /**
     * Generate unique stream ID
     */
    protected function generateStreamId(User $user, $journal = null, $type = 'journal')
    {
        $base = "stream_{$type}_{$user->id}_" . time();
        
        if ($journal) {
            $base .= "_{$journal->id}";
        }
        
        return hash('sha256', $base);
    }
    
    /**
     * Calculate cost for tokens
     */
    public function calculateCost(int $tokens, string $provider): float
    {
        $costPer1k = config("services.{$provider}.cost_per_1k_tokens", 
                      config("ai.cost_per_1k_tokens.{$provider}", 0.0014));
        
        return ($tokens / 1000) * $costPer1k;
    }
    
    /**
     * Get streaming statistics
     */
    public function getStreamingStats(User $user = null)
    {
        $query = AIUsageLog::query();
        
        if ($user) {
            $query->where('user_id', $user->id);
        }
        
        $stats = $query->selectRaw('
            COUNT(*) as total_streams,
            SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as successful_streams,
            SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed_streams,
            SUM(tokens_used) as total_tokens,
            SUM(cost) as total_cost,
            AVG(tokens_used) as avg_tokens_per_stream
        ')->first();
        
        return [
            'total_streams' => $stats->total_streams ?? 0,
            'successful_streams' => $stats->successful_streams ?? 0,
            'failed_streams' => $stats->failed_streams ?? 0,
            'success_rate' => $stats->total_streams > 0 
                ? round(($stats->successful_streams / $stats->total_streams) * 100, 2) 
                : 0,
            'total_tokens' => $stats->total_tokens ?? 0,
            'total_cost' => $stats->total_cost ?? 0,
            'avg_tokens_per_stream' => $stats->avg_tokens_per_stream ?? 0,
        ];
    }
    
    /**
     * Clean up old streams
     */
    public function cleanupOldStreams($days = 7)
    {
        try {
            $deleted = AIUsageLog::where('created_at', '<', now()->subDays($days))
                ->where('status', 'completed')
                ->delete();
                
            return [
                'success' => true,
                'deleted_count' => $deleted,
                'message' => "Deleted {$deleted} old stream records"
            ];
            
        } catch (Exception $e) {
            Log::error('Streaming Service Error - Cleanup', [
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
}