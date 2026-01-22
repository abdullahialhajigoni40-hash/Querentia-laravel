<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Exception;

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
            'http_errors' => false,
        ]);
        
        $this->providers = config('ai.providers');
        $this->defaultProvider = config('ai.default_provider', 'deepseek');
    }
    
    /**
     * Generate journal from sections with streaming support
     * * @param array $sections User-provided section data
     * @param callable|null $streamCallback Function to handle incoming text chunks
     * @param string|null $provider Specific AI provider to use
     */
    public function generateJournalFromSections(array $sections, callable $streamCallback = null, $provider = null)
    {
        $providerKey = $provider ?: $this->defaultProvider;
        $config = $this->providers[$providerKey] ?? $this->providers[$this->defaultProvider];
        
        $prompt = $this->createJournalPrompt($sections);
        $systemPrompt = $this->getSystemPrompt();

        try {
            $response = $this->client->post($config['endpoint'], [
                'headers' => [
                    'Authorization' => 'Bearer ' . $config['key'],
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'model' => $config['model'],
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => $config['temperature'] ?? 0.7,
                    'max_tokens' => $config['max_tokens'] ?? 4000,
                    'stream' => true, // Request streaming from the AI API
                ],
                'stream' => true, // Tell Guzzle to maintain the connection for reading
            ]);

            if ($response->getStatusCode() !== 200) {
                $errorBody = (string) $response->getBody();
                Log::error("AI API Error ({$providerKey}): " . $errorBody);
                throw new Exception("AI provider returned an error. Please check logs.");
            }

            $body = $response->getBody();
            $buffer = '';

            // Read the stream chunk by chunk from the AI provider
            while (!$body->eof()) {
                $chunk = $body->read(1024);
                $buffer .= $chunk;

                // AI providers send data in "data: {...}" format lines
                while (($pos = strpos($buffer, "\n")) !== false) {
                    $line = trim(substr($buffer, 0, $pos));
                    $buffer = substr($buffer, $pos + 1);
                    
                    if (strpos($line, 'data: ') === 0) {
                        $dataText = substr($line, 6);
                        
                        // Check for the end of stream signal
                        if ($dataText === '[DONE]') {
                            break 2;
                        }

                        $decoded = json_decode($dataText, true);
                        
                        // Standard OpenAI/DeepSeek delta format
                        $content = $decoded['choices'][0]['delta']['content'] ?? '';
                        
                        if ($content !== '' && $streamCallback) {
                            $streamCallback($content);
                        }
                    }
                }
            }
        } catch (Exception $e) {
            Log::error('AIJournalService Streaming Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Build the structured prompt for the AI
     */
    private function createJournalPrompt(array $sections): string
    {
        $prompt = "Write a comprehensive academic journal paper based on the following section drafts provided by the researcher. \n\n";
        
        // Loop through provided sections to build context
        foreach ($sections as $key => $value) {
            if (!empty($value) && is_string($value)) {
                $label = ucwords(str_replace('_', ' ', $key));
                $prompt .= "### {$label}:\n{$value}\n\n";
            }
        }

        $prompt .= "--- \nINSTRUCTIONS:\n";
        $prompt .= "1. Use formal academic language.\n";
        $prompt .= "2. Ensure logical transitions between sections.\n";
        $prompt .= "3. Expand on the provided notes to create a full-length manuscript.\n";
        $prompt .= "4. Include a bibliography/references section at the end.";
        
        return $prompt;
    }

    /**
     * Define the AI's persona
     */
    private function getSystemPrompt(): string
    {
        return "You are an AI specialized in academic research writing. You help researchers expand their notes into professional, peer-reviewed quality journal articles. Maintain a neutral, objective, and scholarly tone.";
    }

    /**
     * Helper to calculate improvement metrics (Optional - for logging)
     */
    public function calculateImprovement(string $original, string $enhanced): float
    {
        $originalLength = strlen($original);
        $enhancedLength = strlen($enhanced);
        
        if ($originalLength === 0) return 100.0;
        
        $ratio = ($enhancedLength - $originalLength) / $originalLength;
        return round(min(100, max(0, 50 + ($ratio * 50))), 2);
    }
}