<?php

use Illuminate\Http\Request;
use App\Services\AIJournalService;

Route::get('/test-ai-debug', function() {
    try {
        $aiService = app(AIJournalService::class);
        
        // Test basic configuration
        $config = $aiService->getProviderConfig('deepseek');
        
        return response()->json([
            'status' => 'debug',
            'deepseek_config' => [
                'has_key' => !empty($config['key']),
                'endpoint' => $config['endpoint'] ?? 'not set',
                'model' => $config['model'] ?? 'not set',
                'key_length' => strlen($config['key'] ?? '')
            ],
            'env_check' => [
                'deepseek_key_set' => !empty(env('DEEPSEEK_API_KEY')),
                'key_length' => strlen(env('DEEPSEEK_API_KEY', ''))
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});
