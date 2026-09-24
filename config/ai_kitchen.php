<?php

declare(strict_types=1);

return [
    'enabled' => (bool) env('AI_KITCHEN_ENABLED', true),
    'default_provider' => env('AI_KITCHEN_PROVIDER', 'mock'),
    'fallback_chain' => array_values(array_filter(explode(',', (string) env('AI_KITCHEN_FALLBACK_CHAIN', 'mock')))),
    'timeout_seconds' => (int) env('AI_KITCHEN_TIMEOUT_SECONDS', 12),
    'cache_ttl_seconds' => (int) env('AI_KITCHEN_CACHE_TTL_SECONDS', 900),
    'rate_limit_per_hour' => (int) env('AI_KITCHEN_RATE_LIMIT_PER_HOUR', 30),
    'monthly_cost_limit_cents' => (int) env('AI_KITCHEN_MONTHLY_COST_LIMIT_CENTS', 0),
    'providers' => [
        'mock' => ['enabled' => true, 'model' => 'house-chef-local-v1'],
        'gemini' => ['enabled' => (bool) env('GEMINI_AI_KITCHEN_ENABLED', false), 'model' => env('GEMINI_AI_KITCHEN_MODEL', ''), 'key' => env('GEMINI_API_KEY')],
        'openai' => ['enabled' => (bool) env('OPENAI_AI_KITCHEN_ENABLED', false), 'model' => env('OPENAI_AI_KITCHEN_MODEL', ''), 'key' => env('OPENAI_API_KEY')],
        'claude' => ['enabled' => (bool) env('CLAUDE_AI_KITCHEN_ENABLED', false), 'model' => env('CLAUDE_AI_KITCHEN_MODEL', ''), 'key' => env('ANTHROPIC_API_KEY')],
        'openrouter' => ['enabled' => (bool) env('OPENROUTER_AI_KITCHEN_ENABLED', false), 'model' => env('OPENROUTER_AI_KITCHEN_MODEL', ''), 'key' => env('OPENROUTER_API_KEY')],
        'nvidia' => [
            'enabled' => (bool) env('NVIDIA_AI_KITCHEN_ENABLED', false),
            'model' => env('NVIDIA_AI_KITCHEN_MODEL', 'nvidia/nemotron-3-nano-30b-a3b'),
            'key' => env('NVIDIA_API_KEY'),
            'base_url' => env('NVIDIA_BASE_URL', 'https://integrate.api.nvidia.com/v1'),
            'timeout_seconds' => (int) env('NVIDIA_TIMEOUT_SECONDS', 12),
            'max_tokens' => (int) env('NVIDIA_MAX_TOKENS', 800),
        ],
        'ollama' => ['enabled' => (bool) env('OLLAMA_AI_KITCHEN_ENABLED', false), 'model' => env('OLLAMA_AI_KITCHEN_MODEL', ''), 'url' => env('OLLAMA_URL', 'http://127.0.0.1:11434')],
    ],
];
