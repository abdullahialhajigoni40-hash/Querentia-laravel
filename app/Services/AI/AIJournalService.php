<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Auth;

class AIJournalService
{
    private $client;
    private $providers;
    private $defaultProvider;
    
    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 120,
            'connect_timeout' => 30,
            'http_errors' => false, // Don't throw exceptions on HTTP errors
        ]);
        
        $this->providers = $this->loadProviderConfig();
        $this->defaultProvider = config('ai.default_provider', 'deepseek');
    }
    
    /**
     * Generate journal from sections with streaming support
     */
    public function generateJournalFromSections(array $sections, callable $streamCallback = null, $provider = null)
    {
        $provider = $provider ?: $this->defaultProvider;
        
        // Check rate limiting
        $this->checkRateLimit(Auth::id() ?? 'guest');
        
        // Format the prompt
        $prompt = $this->createJournalPrompt($sections);
        $systemPrompt = $this->getSystemPrompt();
        
        Log::info('Starting AI journal generation', [
            'provider' => $provider,
            'sections_count' => count($sections),
            'prompt_length' => strlen($prompt)
        ]);
        
        try {
            switch ($provider) {
                case 'deepseek':
                    return $this->callDeepSeek($systemPrompt, $prompt, $streamCallback);
                    
                case 'openai':
                    return $this->callOpenAI($systemPrompt, $prompt, $streamCallback);
                    
                case 'gemini':
                    return $this->callGemini($systemPrompt, $prompt, $streamCallback);
                    
                default:
                    throw new \Exception("Unsupported provider: {$provider}");
            }
        } catch (\Exception $e) {
            Log::error('AI generation failed', [
                'provider' => $provider,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Try fallback providers
            return $this->tryFallbackProviders($systemPrompt, $prompt, $streamCallback, $provider);
        }
    }
    
    /**
     * Enhance a single section with streaming support
     */
    public function enhanceSectionStreaming(string $content, string $sectionType, array $context = [], $provider = null, callable $streamCallback)
    {
        $provider = $provider ?: $this->defaultProvider;
        
        // Check rate limiting
        $this->checkRateLimit(Auth::id() ?? 'guest');
        
        // Format the prompt
        $prompt = $this->createEnhancementPrompt($content, $sectionType, $context);
        $systemPrompt = $this->getEnhancementSystemPrompt($sectionType);
        
        Log::info('Starting section enhancement streaming', [
            'provider' => $provider,
            'section_type' => $sectionType,
            'content_length' => strlen($content)
        ]);
        
        try {
            switch ($provider) {
                case 'deepseek':
                    return $this->callDeepSeek($systemPrompt, $prompt, $streamCallback);
                    
                case 'openai':
                    return $this->callOpenAI($systemPrompt, $prompt, $streamCallback);
                    
                case 'gemini':
                    return $this->callGemini($systemPrompt, $prompt, $streamCallback);
                    
                default:
                    throw new \Exception("Unsupported provider: {$provider}");
            }
        } catch (\Exception $e) {
            Log::error('Section enhancement failed', [
                'provider' => $provider,
                'section_type' => $sectionType,
                'error' => $e->getMessage()
            ]);
            
            // Try fallback providers
            return $this->tryFallbackProviders($systemPrompt, $prompt, $streamCallback, $provider);
        }
    }
    
    /**
     * Enhance a single section (non-streaming)
     */
    public function enhanceSection(string $content, string $sectionType, array $context = [], $provider = null): array
    {
        $provider = $provider ?: $this->defaultProvider;
        
        // Check rate limiting
        $this->checkRateLimit(Auth::id() ?? 'guest');
        
        // Format the prompt
        $prompt = $this->createEnhancementPrompt($content, $sectionType, $context);
        $systemPrompt = $this->getEnhancementSystemPrompt($sectionType);
        
        Log::info('Enhancing section', [
            'provider' => $provider,
            'section_type' => $sectionType,
            'content_length' => strlen($content)
        ]);
        
        try {
            $result = null;
            
            switch ($provider) {
                case 'deepseek':
                    $result = $this->callDeepSeek($systemPrompt, $prompt);
                    break;
                    
                case 'openai':
                    $result = $this->callOpenAI($systemPrompt, $prompt);
                    break;
                    
                case 'gemini':
                    $result = $this->callGemini($systemPrompt, $prompt);
                    break;
                    
                default:
                    throw new \Exception("Unsupported provider: {$provider}");
            }
            
            // Analyze the enhanced content
            $suggestions = $this->analyzeSuggestions($content, $result, $sectionType);
            $tokensUsed = $this->estimateTokens($prompt) + $this->estimateTokens($result);
            $estimatedCost = $this->calculateCost($tokensUsed, $provider);
            
            return [
                'content' => $result,
                'suggestions' => $suggestions,
                'tokens_used' => $tokensUsed,
                'estimated_cost' => $estimatedCost,
                'provider' => $provider,
                'original_length' => strlen($content),
                'enhanced_length' => strlen($result),
                'improvement_percentage' => $this->calculateImprovement($content, $result, $sectionType),
            ];
            
        } catch (\Exception $e) {
            Log::error('Section enhancement failed', [
                'provider' => $provider,
                'section_type' => $sectionType,
                'error' => $e->getMessage()
            ]);
            
            // Try fallback providers
            $fallbackProviders = array_diff(['deepseek', 'openai', 'gemini'], [$provider]);
            
            foreach ($fallbackProviders as $fallbackProvider) {
                try {
                    Log::info('Trying fallback provider', ['provider' => $fallbackProvider]);
                    
                    switch ($fallbackProvider) {
                        case 'deepseek':
                            $result = $this->callDeepSeek($systemPrompt, $prompt);
                            break;
                        case 'openai':
                            $result = $this->callOpenAI($systemPrompt, $prompt);
                            break;
                        case 'gemini':
                            $result = $this->callGemini($systemPrompt, $prompt);
                            break;
                    }
                    
                    return [
                        'content' => $result,
                        'suggestions' => $this->analyzeSuggestions($content, $result, $sectionType),
                        'tokens_used' => $this->estimateTokens($prompt) + $this->estimateTokens($result),
                        'estimated_cost' => $this->calculateCost($this->estimateTokens($prompt) + $this->estimateTokens($result), $fallbackProvider),
                        'provider' => $fallbackProvider,
                        'original_length' => strlen($content),
                        'enhanced_length' => strlen($result),
                    ];
                    
                } catch (\Exception $fallbackError) {
                    continue;
                }
            }
            
            throw new \Exception('All AI providers failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Improve existing journal with feedback
     */
    public function improveJournal(string $currentContent, string $prompt, $provider = null): string
    {
        $provider = $provider ?: $this->defaultProvider;
        
        // Check rate limiting
        $this->checkRateLimit(Auth::id() ?? 'guest');
        
        $systemPrompt = $this->getImprovementSystemPrompt();
        
        Log::info('Improving journal with feedback', [
            'provider' => $provider,
            'current_length' => strlen($currentContent)
        ]);
        
        try {
            switch ($provider) {
                case 'deepseek':
                    return $this->callDeepSeek($systemPrompt, $prompt);
                    
                case 'openai':
                    return $this->callOpenAI($systemPrompt, $prompt);
                    
                case 'gemini':
                    return $this->callGemini($systemPrompt, $prompt);
                    
                default:
                    throw new \Exception("Unsupported provider: {$provider}");
            }
        } catch (\Exception $e) {
            Log::error('Journal improvement failed', [
                'provider' => $provider,
                'error' => $e->getMessage()
            ]);
            
            // Try fallback providers
            $fallbackProviders = array_diff(['deepseek', 'openai', 'gemini'], [$provider]);
            
            foreach ($fallbackProviders as $fallbackProvider) {
                try {
                    switch ($fallbackProvider) {
                        case 'deepseek':
                            return $this->callDeepSeek($systemPrompt, $prompt);
                        case 'openai':
                            return $this->callOpenAI($systemPrompt, $prompt);
                        case 'gemini':
                            return $this->callGemini($systemPrompt, $prompt);
                    }
                } catch (\Exception $fallbackError) {
                    continue;
                }
            }
            
            throw new \Exception('All AI providers failed');
        }
    }
    
    /**
     * Test a specific provider
     */
    public function testProvider(string $provider): array
    {
        if (!in_array($provider, ['deepseek', 'openai', 'gemini'])) {
            throw new \Exception("Unsupported provider: {$provider}");
        }
        
        $config = $this->getProviderConfig($provider);
        
        if (empty($config['key'])) {
            return [
                'success' => false,
                'message' => 'Provider not configured',
                'configured' => false,
            ];
        }
        
        try {
            $testPrompt = 'Respond with exactly "OK" if you are working.';
            $startTime = microtime(true);
            
            switch ($provider) {
                case 'deepseek':
                    $result = $this->callDeepSeek('Test', $testPrompt);
                    break;
                case 'openai':
                    $result = $this->callOpenAI('Test', $testPrompt);
                    break;
                case 'gemini':
                    $result = $this->callGemini('Test', $testPrompt);
                    break;
            }
            
            $responseTime = microtime(true) - $startTime;
            
            $isWorking = trim($result) === 'OK';
            
            return [
                'success' => $isWorking,
                'configured' => true,
                'working' => $isWorking,
                'response_time' => round($responseTime, 2),
                'message' => $isWorking ? 'Provider is working correctly' : 'Unexpected response: ' . substr($result, 0, 100),
                'raw_response' => $result,
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'configured' => true,
                'working' => false,
                'message' => $e->getMessage(),
                'error_type' => get_class($e),
            ];
        }
    }
    
    /**
     * Get user usage statistics
     */
    public function getUserUsageStats($userId): array
    {
        // This would normally query a database
        // For now, return mock data or integrate with AIUsageLog model
        
        return [
            'total_tokens_used' => 0,
            'total_cost' => 0,
            'total_requests' => 0,
            'by_provider' => [],
            'by_task_type' => [],
            'daily_usage' => [],
        ];
    }
    
    /**
     * Get provider status
     */
    public function getProviderStatus(): array
    {
        $status = [];
        
        foreach (['deepseek', 'openai', 'gemini'] as $provider) {
            $config = $this->getProviderConfig($provider);
            
            $status[$provider] = [
                'configured' => !empty($config['key']),
                'endpoint' => $config['endpoint'] ?? null,
                'model' => $config['model'] ?? null,
                'max_tokens' => $config['max_tokens'] ?? 4000,
                'temperature' => $config['temperature'] ?? 0.7,
            ];
        }
        
        return $status;
    }
    
    /**
     * Get usage statistics
     */
    public function getUsageStats(): array
    {
        return [
            'default_provider' => $this->defaultProvider,
            'max_tokens' => config('ai.max_tokens', 4000),
            'temperature' => config('ai.temperature', 0.7),
            'providers' => $this->getProviderStatus(),
            'rate_limit' => config('ai.rate_limit', 100),
            'requires_subscription' => config('ai.requires_subscription', false),
        ];
    }
    
    /**
     * =================================================================
     * PRIVATE CORE METHODS
     * =================================================================
     */
    
    private function callDeepSeek(string $systemPrompt, string $userPrompt, callable $streamCallback = null)
    {
        $config = $this->getProviderConfig('deepseek');
        
        if (!$config || empty($config['key'])) {
            throw new \Exception('DeepSeek API key not configured');
        }
        
        $payload = [
            'model' => $config['model'] ?? 'deepseek-chat',
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt]
            ],
            'temperature' => (float) ($config['temperature'] ?? 0.7),
            'max_tokens' => (int) ($config['max_tokens'] ?? 4000),
            'stream' => $streamCallback !== null,
        ];
        
        if ($streamCallback) {
            return $this->streamRequest($config['endpoint'], $config['key'], $payload, $streamCallback, 'deepseek');
        } else {
            return $this->makeRequest($config['endpoint'], $config['key'], $payload, 'deepseek');
        }
    }
    
    private function callOpenAI(string $systemPrompt, string $userPrompt, callable $streamCallback = null)
    {
        $config = $this->getProviderConfig('openai');
        
        if (!$config || empty($config['key'])) {
            throw new \Exception('OpenAI API key not configured');
        }
        
        $payload = [
            'model' => $config['model'] ?? 'gpt-4-turbo-preview',
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt]
            ],
            'temperature' => (float) ($config['temperature'] ?? 0.7),
            'max_tokens' => (int) ($config['max_tokens'] ?? 4000),
            'stream' => $streamCallback !== null,
        ];
        
        if ($streamCallback) {
            return $this->streamRequest($config['endpoint'], $config['key'], $payload, $streamCallback, 'openai');
        } else {
            return $this->makeRequest($config['endpoint'], $config['key'], $payload, 'openai');
        }
    }
    
    private function callGemini(string $systemPrompt, string $userPrompt, callable $streamCallback = null)
    {
        $config = $this->getProviderConfig('gemini');
        
        if (!$config || empty($config['key'])) {
            throw new \Exception('Gemini API key not configured');
        }
        
        // Gemini uses different format
        $endpoint = $config['endpoint'] . "?key={$config['key']}";
        
        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $systemPrompt . "\n\n" . $userPrompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => (float) ($config['temperature'] ?? 0.7),
                'maxOutputTokens' => (int) ($config['max_tokens'] ?? 4000),
            ]
        ];
        
        // Gemini doesn't support streaming in the same way
        if ($streamCallback) {
            return $this->simulateStreaming($endpoint, $payload, $streamCallback, 'gemini');
        } else {
            return $this->makeGeminiRequest($endpoint, $payload);
        }
    }
    
    private function makeRequest(string $endpoint, string $apiKey, array $payload, string $provider): string
    {
        try {
            $response = $this->client->post($endpoint, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);
            
            $result = json_decode($response->getBody(), true);
            
            if (isset($result['error'])) {
                throw new \Exception($result['error']['message']);
            }
            
            // Different providers have different response structures
            if ($provider === 'gemini') {
                return $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
            } else {
                return $result['choices'][0]['message']['content'] ?? '';
            }
            
        } catch (RequestException $e) {
            $this->handleRequestError($e, $provider);
            throw $e;
        }
    }
    
    private function streamRequest(string $endpoint, string $apiKey, array $payload, callable $callback, string $provider): string
    {
        $fullContent = '';
        
        try {
            $response = $this->client->post($endpoint, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'text/event-stream',
                ],
                'json' => $payload,
                'stream' => true,
            ]);
            
            $stream = $response->getBody();
            
            while (!$stream->eof()) {
                $chunk = $stream->read(1024);
                $lines = explode("\n", $chunk);
                
                foreach ($lines as $line) {
                    $line = trim($line);
                    
                    if (strpos($line, 'data: ') === 0) {
                        $data = substr($line, 6);
                        
                        if ($data === '[DONE]') {
                            $callback(null, true);
                            break 2;
                        }
                        
                        $json = json_decode($data, true);
                        
                        if (isset($json['choices'][0]['delta']['content'])) {
                            $content = $json['choices'][0]['delta']['content'];
                            $fullContent .= $content;
                            
                            $callback($content, false);
                        }
                    }
                }
                
                // Flush output
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
                
                // Check connection
                if (connection_aborted()) {
                    Log::warning('Client disconnected during streaming');
                    break;
                }
            }
            
            return $fullContent;
            
        } catch (RequestException $e) {
            $this->handleRequestError($e, $provider);
            throw $e;
        }
    }
    
    private function makeGeminiRequest(string $endpoint, array $payload): string
    {
        try {
            $response = $this->client->post($endpoint, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);
            
            $result = json_decode($response->getBody(), true);
            
            if (isset($result['error'])) {
                throw new \Exception($result['error']['message']);
            }
            
            return $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
            
        } catch (RequestException $e) {
            $this->handleRequestError($e, 'gemini');
            throw $e;
        }
    }
    
    private function simulateStreaming(string $endpoint, array $payload, callable $callback, string $provider): string
    {
        try {
            $response = $this->client->post($endpoint, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);
            
            $result = json_decode($response->getBody(), true);
            $content = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
            
            // Simulate streaming by sending in chunks
            $chunks = str_split($content, 30); // 30 chars per chunk
            
            foreach ($chunks as $chunk) {
                $callback($chunk, false);
                
                // Small delay to simulate streaming
                usleep(50000); // 50ms
                
                if (connection_aborted()) {
                    break;
                }
            }
            
            $callback(null, true);
            return $content;
            
        } catch (RequestException $e) {
            $this->handleRequestError($e, $provider);
            throw $e;
        }
    }
    
    private function tryFallbackProviders(string $systemPrompt, string $userPrompt, callable $streamCallback = null, string $failedProvider)
    {
        $providers = ['deepseek', 'openai', 'gemini'];
        $providers = array_diff($providers, [$failedProvider]);
        
        foreach ($providers as $provider) {
            try {
                Log::info('Trying fallback provider', ['provider' => $provider]);
                
                switch ($provider) {
                    case 'deepseek':
                        if ($streamCallback) {
                            return $this->callDeepSeek($systemPrompt, $userPrompt, $streamCallback);
                        } else {
                            return $this->callDeepSeek($systemPrompt, $userPrompt);
                        }
                        
                    case 'openai':
                        if ($streamCallback) {
                            return $this->callOpenAI($systemPrompt, $userPrompt, $streamCallback);
                        } else {
                            return $this->callOpenAI($systemPrompt, $userPrompt);
                        }
                        
                    case 'gemini':
                        if ($streamCallback) {
                            return $this->callGemini($systemPrompt, $userPrompt, $streamCallback);
                        } else {
                            return $this->callGemini($systemPrompt, $userPrompt);
                        }
                }
            } catch (\Exception $e) {
                Log::error('Fallback provider failed', [
                    'provider' => $provider,
                    'error' => $e->getMessage()
                ]);
                continue;
            }
        }
        
        throw new \Exception('All AI providers failed');
    }
    
    /**
     * =================================================================
     * PROMPT GENERATION METHODS
     * =================================================================
     */
    
    private function getSystemPrompt(): string
    {
        return Cache::remember('ai_system_prompt_journal', 3600, function () {
            return <<<PROMPT
You are Querentia's academic journal generation AI. You transform research content into publication-ready journals following EXACT formatting rules.

CRITICAL RULES:
- Maintain 80% of original user wording
- Fill missing sections with appropriate content
- Ensure academic tone and proper grammar
- Add relevant citations if missing
- Generate tables/figures from provided data
- Output COMPLETE journal ready for publication
- Format for academic journal publication
PROMPT;
        });
    }
    
    private function getEnhancementSystemPrompt(string $sectionType): string
    {
        $prompts = [
            'abstract' => 'You are an expert academic editor specializing in abstract writing. Enhance the abstract to be concise, informative, and structured (Background, Objective, Methods, Key Findings, Conclusion).',
            'introduction' => 'You are an expert academic editor specializing in introduction writing. Enhance the introduction to provide proper background, state research gap, and present research question/hypothesis clearly.',
            'methodology' => 'You are an expert academic editor specializing in methodology writing. Enhance the methodology to be detailed, reproducible, and follow standard academic conventions.',
            'results' => 'You are an expert academic editor specializing in results presentation. Enhance the results to be clear, objective, and properly formatted with appropriate statistical reporting.',
            'conclusion' => 'You are an expert academic editor specializing in conclusion writing. Enhance the conclusion to summarize key findings, discuss implications, and suggest future research.',
            'references' => 'You are an expert academic editor specializing in citation formatting. Format the references in proper academic style (APA by default).',
            'grammar' => 'You are an expert grammar and style editor. Correct grammar, spelling, punctuation, and improve sentence structure while maintaining the original meaning.',
            'summarize' => 'You are an expert summarizer. Create a concise summary while preserving key information and academic tone.',
            'enhance' => 'You are an expert academic editor. Enhance the writing quality, improve clarity, strengthen arguments, and ensure academic rigor.',
        ];
        
        return $prompts[$sectionType] ?? 'You are an expert academic editor. Enhance the writing quality and ensure academic standards.';
    }
    
    private function getImprovementSystemPrompt(): string
    {
        return <<<PROMPT
You are an expert academic journal editor. You improve journals based on peer feedback while:
1. Maintaining 70% of original human-written content
2. Addressing all feedback points specifically
3. Improving clarity, structure, and academic rigor
4. Preserving the original research contributions
5. Ensuring logical flow between sections
PROMPT;
    }
    
    private function createJournalPrompt(array $sections): string
    {
        $sectionNames = [
            'title' => 'RESEARCH TOPIC',
            'authors' => 'AUTHORS',
            'abstract' => 'ABSTRACT',
            'introduction' => 'INTRODUCTION',
            'area_of_study' => 'AREA OF STUDY',
            'additional_notes' => 'ADDITIONAL NOTES',
            'materials_methods' => 'MATERIALS & METHODS',
            'results_discussion' => 'RESULTS & DISCUSSION',
            'conclusion' => 'CONCLUSION',
            'references' => 'REFERENCES',
            'annexes' => 'ANNEXES',
            'maps_figures' => 'MAPS & FIGURES',
        ];
        
        $prompt = "Generate a complete academic journal manuscript based on the following research content:\n\n";
        
        foreach ($sectionNames as $key => $name) {
            if (!empty($sections[$key])) {
                $content = is_array($sections[$key]) ? json_encode($sections[$key]) : $sections[$key];
                $prompt .= "=== {$name} ===\n{$content}\n\n";
            }
        }
        
        $prompt .= "\nINSTRUCTIONS:\n";
        $prompt .= "1. Generate a COMPLETE journal following academic format\n";
        $prompt .= "2. Fill any missing sections with appropriate academic content\n";
        $prompt .= "3. Ensure logical flow between sections\n";
        $prompt .= "4. Add tables/figures where appropriate\n";
        $prompt .= "5. Include proper citations and references\n";
        $prompt .= "6. Maintain 80% of original user wording\n";
        $prompt .= "7. Output should be publication-ready\n";
        
        return $prompt;
    }
    
    private function createEnhancementPrompt(string $content, string $sectionType, array $context): string
    {
        $basePrompt = "Enhance the following {$sectionType} section:\n\n{$content}\n\n";
        
        // Add context if available
        if (!empty($context)) {
            $basePrompt .= "Context:\n";
            foreach ($context as $key => $value) {
                if (is_string($value)) {
                    $basePrompt .= "- {$key}: {$value}\n";
                }
            }
            $basePrompt .= "\n";
        }
        
        // Add section-specific instructions
        $instructions = [
            'abstract' => 'Make it 150-250 words. Include: Background, Objective, Methods, Key Findings, Conclusion.',
            'introduction' => 'Start broad, then narrow to specific research question. Include literature review context.',
            'methodology' => 'Be detailed enough for reproducibility. Include study design, procedures, analysis methods.',
            'results' => 'Present results objectively. Include relevant data, statistics, and figures.',
            'conclusion' => 'Summarize key findings, state limitations, suggest future research directions.',
            'references' => 'Format in APA style: Author, A. A. (Year). Title. Journal, Volume(Issue), Pages.',
            'grammar' => 'Correct grammar, spelling, punctuation. Improve sentence structure and clarity.',
            'summarize' => 'Create concise summary while preserving key information.',
            'enhance' => 'Improve writing quality, strengthen arguments, ensure academic rigor.',
        ];
        
        if (isset($instructions[$sectionType])) {
            $basePrompt .= "Specific requirements: {$instructions[$sectionType]}\n\n";
        }
        
        $basePrompt .= "Enhanced version:";
        
        return $basePrompt;
    }
    
    /**
     * =================================================================
     * UTILITY METHODS
     * =================================================================
     */
    
    private function loadProviderConfig(): array
    {
        return [
            'deepseek' => [
                'key' => config('services.deepseek.key'),
                'endpoint' => config('services.deepseek.endpoint', 'https://api.deepseek.com/v1/chat/completions'),
                'model' => config('services.deepseek.model', 'deepseek-chat'),
                'max_tokens' => config('services.deepseek.max_tokens', 4000),
                'temperature' => config('services.deepseek.temperature', 0.7),
            ],
            'openai' => [
                'key' => config('services.openai.key'),
                'endpoint' => config('services.openai.endpoint', 'https://api.openai.com/v1/chat/completions'),
                'model' => config('services.openai.model', 'gpt-4-turbo-preview'),
                'max_tokens' => config('services.openai.max_tokens', 4000),
                'temperature' => config('services.openai.temperature', 0.7),
            ],
            'gemini' => [
                'key' => config('services.gemini.key'),
                'endpoint' => config('services.gemini.endpoint', 'https://generativelanguage.googleapis.com/v1/models/gemini-pro:generateContent'),
                'model' => config('services.gemini.model', 'gemini-pro'),
                'max_tokens' => config('services.gemini.max_tokens', 4000),
                'temperature' => config('services.gemini.temperature', 0.7),
            ],
        ];
    }
    
    private function getProviderConfig(string $provider): array
    {
        return $this->providers[$provider] ?? [];
    }
    
    private function checkRateLimit(string $userId): void
    {
        $key = 'ai_rate_limit:' . $userId;
        $maxAttempts = config('ai.rate_limit', 100);
        $decayMinutes = 60;
        
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            throw new \Exception("Rate limit exceeded. Please try again in {$seconds} seconds.");
        }
        
        RateLimiter::hit($key, $decayMinutes * 60);
    }
    
    private function handleRequestError(RequestException $e, string $provider): void
    {
        $statusCode = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 0;
        $responseBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : '';
        
        Log::error("{$provider} API request failed", [
            'status_code' => $statusCode,
            'error' => $e->getMessage(),
            'response' => $responseBody,
        ]);
        
        // Provide more user-friendly error messages
        if ($statusCode === 401) {
            throw new \Exception("{$provider} API key is invalid or expired");
        } elseif ($statusCode === 429) {
            throw new \Exception("{$provider} rate limit exceeded. Please try again later.");
        } elseif ($statusCode >= 500) {
            throw new \Exception("{$provider} service is temporarily unavailable");
        }
    }
    
    private function estimateTokens(string $text): int
    {
        // Rough estimate: 1 token ≈ 4 characters for English text
        return ceil(strlen($text) / 4);
    }
    
    private function calculateCost(int $tokens, string $provider): float
    {
        $costPer1KTokens = [
            'deepseek' => 0.0014,
            'openai' => 0.03,
            'gemini' => 0.00125,
        ];
        
        $rate = $costPer1KTokens[$provider] ?? 0.001;
        $cost = ($tokens / 1000) * $rate;
        
        return round($cost, 6);
    }
    
    private function analyzeSuggestions(string $original, string $enhanced, string $sectionType): array
    {
        $suggestions = [];
        
        // Grammar and spelling improvements
        if ($sectionType === 'grammar') {
            $suggestions[] = 'Grammar and spelling corrections applied';
        }
        
        // Length comparison
        $originalWords = str_word_count($original);
        $enhancedWords = str_word_count($enhanced);
        
        if ($enhancedWords > $originalWords) {
            $suggestions[] = "Expanded from {$originalWords} to {$enhancedWords} words for better detail";
        } elseif ($enhancedWords < $originalWords) {
            $suggestions[] = "Condensed from {$originalWords} to {$enhancedWords} words for conciseness";
        }
        
        // Section-specific suggestions
        $sectionSuggestions = [
            'abstract' => ['Added standard abstract structure', 'Improved clarity and conciseness'],
            'introduction' => ['Strengthened research gap statement', 'Improved literature context'],
            'methodology' => ['Enhanced reproducibility details', 'Improved procedure descriptions'],
            'results' => ['Better data presentation', 'Improved statistical reporting'],
            'conclusion' => ['Strengthened implications', 'Added future research directions'],
        ];
        
        if (isset($sectionSuggestions[$sectionType])) {
            $suggestions = array_merge($suggestions, $sectionSuggestions[$sectionType]);
        }
        
        return array_unique($suggestions);
    }
    
    private function calculateImprovement(string $original, string $enhanced, string $sectionType): float
    {
        // Simple improvement calculation based on length and content changes
        $originalLength = strlen($original);
        $enhancedLength = strlen($enhanced);
        
        if ($originalLength === 0) return 100;
        
        $lengthRatio = ($enhancedLength - $originalLength) / $originalLength;
        
        // Normalize to 0-100 scale
        $improvement = min(100, max(0, 50 + ($lengthRatio * 50)));
        
        return round($improvement, 2);
    }
}