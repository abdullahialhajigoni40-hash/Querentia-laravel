<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
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
    public function streamJournal(Request $request, $journalId = null)
    {
        // Allow long-running streaming requests to run without PHP timeout
        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }

        // For GET requests (EventSource), get data from query parameters
        // For POST requests, get data from request body
        if ($request->isMethod('GET')) {
            $sections = json_decode($request->query('sections', '[]'), true);
            $provider = $request->query('provider', config('ai.default_provider', 'deepseek'));
        } else {
            // 1. Validation (avoid redirects for fetch/SSE calls)
            $validator = Validator::make($request->all(), [
                'sections' => 'required|array',
                'provider' => 'nullable|string|in:deepseek,openai,gemini,anthropic',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid request payload for AI streaming.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $sections = $request->input('sections', []);
            $provider = $request->input('provider', config('ai.default_provider', 'deepseek'));
        }

        // Validate sections data
        if (!is_array($sections) || empty($sections)) {
            return response()->json([
                'success' => false,
                'message' => 'Sections data is required and must be an array.',
            ], 422);
        }

        // Log incoming request for debugging
        Log::info('AI stream request received', [
            'method' => $request->method(),
            'provider' => $provider,
            'sections_count' => count($sections),
            'journal_id' => $journalId,
            'has_auth' => Auth::check(),
        ]);

        return new StreamedResponse(function() use ($sections, $provider, $journalId) {
            // Setup SSE Headers helper
            $sendEvent = function($event, $data) {
                echo "event: {$event}\n";
                echo "data: " . json_encode($data) . "\n\n";
                ob_flush();
                flush();
            };

            try {
                // Phase 1: Signal Start
                $sendEvent('start', [
                    'status' => 'connected',
                    'message' => 'Initializing AI researcher...',
                    'journal_id' => $journalId
                ]);

                // Phase 2: Generate content using actual AI service with streaming
                $fullGeneratedContent = '';
                
                // Use AI service with streaming callback
                $this->aiService->generateJournalFromSections($sections, function($chunk) use ($sendEvent, &$fullGeneratedContent) {
                    $fullGeneratedContent .= $chunk;
                    $sendEvent('chunk', ['content' => $chunk]);
                    usleep(100000); // 100ms delay for streaming effect
                }, $provider);

                // Phase 3: Signal Completion
                $sendEvent('complete', [
                    'journal_id' => $journalId,
                    'status' => 'success',
                    'message' => 'Journal successfully generated.'
                ]);

            } catch (Exception $e) {
                Log::error('AI streaming error', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $sendEvent('server-error', [
                    'message' => 'Generation interrupted: ' . $e->getMessage()
                ]);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Build the specialized prompt for the AI
     */
    private function buildPrompt(array $sections): string
    {
        $prompt = "Create a professional academic journal article based on these notes:\n\n";
        
        foreach ($sections as $section) {
            if (isset($section['content']) && !empty($section['content'])) {
                $title = $section['title'] ?? 'Section';
                $content = $section['content'];
                $prompt .= "--- {$title} ---\n{$content}\n\n";
            }
        }
        
        $prompt .= "\nStructure the output with the following standard sections:\n";
        $prompt .= "1. Title\n2. Abstract\n3. Introduction\n4. Methodology\n5. Results\n6. Discussion\n7. Conclusion\n8. References\n\n";
        $prompt .= "IMPORTANT: Use the provided notes to create meaningful content for each section. ";
        $prompt .= "Do not make up information that wasn't provided. ";
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