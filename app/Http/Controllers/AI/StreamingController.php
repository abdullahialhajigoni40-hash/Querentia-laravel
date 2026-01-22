<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\Journal;
use App\Models\AIUsageLog;
use App\Services\AIJournalService;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;
use Exception;

class StreamingController extends Controller
{
    protected $aiService;

    public function __construct(AIJournalService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Stream journal generation with Server-Sent Events (SSE)
     * Handles the full lifecycle: Connection -> Streaming -> Saving -> Logging
     */
    public function streamJournal(Request $request, $journalId = null): StreamedResponse
    {
        // 1. Validation
        $request->validate([
            'sections' => 'required|array',
            'provider' => 'nullable|string|in:deepseek,openai,gemini,anthropic',
        ]);

        $user = Auth::user();
        $sections = $request->input('sections', []);
        $provider = $request->input('provider', config('ai.default_provider', 'deepseek'));
        
        // 2. Find or Initialize Journal
        if ($journalId) {
            $journal = Journal::where('id', $journalId)
                ->where('user_id', $user->id)
                ->firstOrFail();
        } else {
            $journal = new Journal();
            $journal->user_id = $user->id;
        }

        return new StreamedResponse(function() use ($sections, $provider, $journal, $user) {
            // Setup SSE Headers helper
            $sendEvent = function($event, $data) {
                echo "event: {$event}\n";
                echo "data: " . json_encode($data) . "\n\n";
                ob_flush(); // Add this
                flush();    // Add this
                if (ob_get_level() > 0) ob_flush();
                flush();
            };

            $fullGeneratedContent = '';

            try {
                // Phase 1: Signal Start
                $sendEvent('start', [
                    'status' => 'connected',
                    'message' => 'Initializing AI researcher...',
                    'journal_id' => $journal->id
                ]);

                // Phase 2: Stream from AI Service
                $this->aiService->generateJournalFromSections(
                    $sections, 
                    function($chunk) use (&$fullGeneratedContent, $sendEvent) {
                        $fullGeneratedContent .= $chunk;
                        $sendEvent('chunk', ['content' => $chunk]);
                    },
                    $provider
                );

                // Phase 3: Post-Processing & Database Storage
                $tokenCount = $this->estimateTokens($fullGeneratedContent);
                $abstract = $this->extractAbstract($fullGeneratedContent);

                $journal->fill([
                    'title' => $sections['title'] ?? 'Untitled Research',
                    'abstract' => $abstract,
                    'ai_generated_content' => $fullGeneratedContent,
                    'ai_provider_used' => $provider,
                    'status' => 'draft',
                    'completed_at' => now(),
                    'raw_content' => $sections, // Store original inputs
                ]);
                
                $journal->save();

                // Phase 4: Usage Logging & Analytics
                $cost = $this->calculateCost($tokenCount, $provider);
                
                AIUsageLog::create([
                    'user_id' => $user->id,
                    'journal_id' => $journal->id,
                    'provider' => $provider,
                    'tokens_used' => $tokenCount,
                    'content_type' => 'full_journal',
                    'cost' => $cost,
                    'metadata' => json_encode([
                        'section_count' => count($sections),
                        'char_count' => strlen($fullGeneratedContent)
                    ])
                ]);

                // Phase 5: Signal Completion
                $sendEvent('complete', [
                    'journal_id' => $journal->id,
                    'status' => 'success',
                    'message' => 'Journal successfully synthesized and saved.'
                ]);

            } catch (Exception $e) {
                Log::error('StreamingController Error: ' . $e->getMessage(), [
                    'user_id' => $user->id,
                    'trace' => $e->getTraceAsString()
                ]);
                
                $sendEvent('error', [
                    'message' => 'Generation interrupted: ' . $e->getMessage()
                ]);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no', // Critical for Nginx
        ]);
    }

    /**
     * Build the specialized prompt for the AI
     */
    private function buildPrompt(array $sections): string
    {
        $prompt = "Create a professional academic journal article based on these notes:\n\n";
        
        foreach ($sections as $title => $content) {
            if (!empty($content)) {
                $label = strtoupper(str_replace('_', ' ', $title));
                $prompt .= "--- {$label} ---\n{$content}\n\n";
            }
        }
        
        $prompt .= "Structure the output with the following standard sections:\n";
        $prompt .= "1. Title\n2. Abstract\n3. Introduction\n4. Methodology\n5. Results\n6. Discussion\n7. Conclusion\n8. References\n\n";
        $prompt .= "Maintain a scholarly tone and ensure all internal citations are consistent.";
        
        return $prompt;
    }

    /**
     * Extract or generate an abstract from the full content
     */
    private function extractAbstract(string $content): string
    {
        // Try to locate abstract using regex
        $patterns = [
            '/Abstract[:\s\n]+(.*?)(?=\n\s*\n|Introduction|1\.0|$)/si',
            '/ABSTRACT[:\s\n]+(.*?)(?=\n\s*\n|INTRODUCTION|1\.0|$)/si',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                $abstract = trim($matches[1]);
                if (strlen($abstract) > 100) return $abstract;
            }
        }
        
        // Fallback: take the first 300 characters
        return substr(strip_tags($content), 0, 300) . '...';
    }

    /**
     * Calculate cost based on current provider rates
     */
    private function calculateCost(int $tokens, string $provider): float
    {
        $rates = config('ai.cost_per_1k_tokens', [
            'deepseek' => 0.0002,
            'openai'   => 0.01,
            'gemini'   => 0.0005,
        ]);

        $rate = $rates[$provider] ?? $rates['deepseek'];
        return ($tokens / 1000) * $rate;
    }

    /**
     * Estimate token count (4 characters approx 1 token)
     */
    private function estimateTokens(string $content): int
    {
        return (int) ceil(strlen($content) / 4);
    }

    /**
     * Rate limiting check to prevent abuse
     */
    private function checkRateLimit($userId)
    {
        $key = 'ai_gen_limit_' . $userId;
        $attempts = Cache::get($key, 0);

        if ($attempts >= 10) { // Limit to 10 generations per hour
            throw new Exception("Hourly generation limit reached. Please try again later.");
        }

        Cache::put($key, $attempts + 1, Carbon::now()->addHour());
    }
}