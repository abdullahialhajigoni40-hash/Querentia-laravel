<?php

use Illuminate\Http\Request;

Route::get('/test-ai-simple', function() {
    try {
        $aiService = app(\App\Services\AIJournalService::class);
        
        // Test simple generation
        $result = $aiService->testProvider('deepseek');
        
        return response()->json([
            'status' => 'success',
            'message' => 'AI service test completed',
            'result' => $result
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});
