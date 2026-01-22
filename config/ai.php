<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default AI Provider
    |--------------------------------------------------------------------------
    |
    | This option controls the default AI provider that will be used
    | when generating content. You can override this per request.
    |
    */
    'default_provider' => env('DEFAULT_AI_PROVIDER', 'deepseek'),

    /*
    |--------------------------------------------------------------------------
    | AI Providers Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for each AI provider. Make sure to set the API keys
    | in your .env file.
    |
    */
    'providers' => [
        'deepseek' => [
            'key' => env('DEEPSEEK_API_KEY'),
            'endpoint' => env('DEEPSEEK_ENDPOINT', 'https://api.deepseek.com/v1/chat/completions'),
            'model' => env('DEEPSEEK_MODEL', 'deepseek-chat'),
            'max_tokens' => env('DEEPSEEK_MAX_TOKENS', 4000),
            'temperature' => env('DEEPSEEK_TEMPERATURE', 0.7),
        ],
        
        'openai' => [
            'key' => env('OPENAI_API_KEY'),
            'endpoint' => env('OPENAI_ENDPOINT', 'https://api.openai.com/v1/chat/completions'),
            'model' => env('OPENAI_MODEL', 'gpt-4-turbo-preview'),
            'max_tokens' => env('OPENAI_MAX_TOKENS', 4000),
            'temperature' => env('OPENAI_TEMPERATURE', 0.7),
        ],
        
        'gemini' => [
            'key' => env('GOOGLE_GEMINI_API_KEY'),
            'endpoint' => env('GEMINI_ENDPOINT', 'https://generativelanguage.googleapis.com/v1/models/gemini-pro:generateContent'),
            'model' => env('GEMINI_MODEL', 'gemini-pro'),
            'max_tokens' => env('GEMINI_MAX_TOKENS', 4000),
            'temperature' => env('GEMINI_TEMPERATURE', 0.7),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Fallback Settings
    |--------------------------------------------------------------------------
    |
    | When the primary provider fails, should we try fallback providers?
    |
    */
    'fallback_enabled' => env('AI_FALLBACK_ENABLED', true),
    'fallback_order' => ['deepseek', 'openai', 'gemini'],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Rate limiting for AI requests per user.
    |
    */
    'rate_limit' => env('AI_RATE_LIMIT', 100),
    'rate_limit_period' => 60,

    /*
    |--------------------------------------------------------------------------
    | Content Rules
    |--------------------------------------------------------------------------
    |
    | Rules for AI-generated content.
    |
    */
    'max_ai_percentage' => env('MAX_AI_PERCENTAGE', 30),
    'min_human_content_words' => env('MIN_HUMAN_CONTENT_WORDS', 100),
    'max_journal_length' => env('MAX_JOURNAL_LENGTH', 10000),

    /*
    |--------------------------------------------------------------------------
    | Cost Settings
    |--------------------------------------------------------------------------
    |
    | Cost per 1K tokens for each provider (in USD).
    |
    */
    'cost_per_1k_tokens' => [
        'deepseek' => 0.0014,
        'openai' => 0.03,
        'gemini' => 0.00125,
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | Enable/disable specific AI features.
    |
    */
    'features' => [
        'journal_generation' => env('AI_FEATURE_JOURNAL_GENERATION', true),
        'section_enhancement' => env('AI_FEATURE_SECTION_ENHANCEMENT', true),
        'grammar_check' => env('AI_FEATURE_GRAMMAR_CHECK', true),
        'summarization' => env('AI_FEATURE_SUMMARIZATION', true),
        'reference_suggestion' => env('AI_FEATURE_REFERENCE_SUGGESTION', true),
        'feedback_improvement' => env('AI_FEATURE_FEEDBACK_IMPROVEMENT', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Subscription Requirements
    |--------------------------------------------------------------------------
    |
    | Whether AI features require a subscription.
    |
    */
    'requires_subscription' => env('AI_REQUIRES_SUBSCRIPTION', false),
    'free_tier_limit' => env('AI_FREE_TIER_LIMIT', 10),
];