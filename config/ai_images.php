<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Pilot safety controls
    |--------------------------------------------------------------------------
    |
    | A zero budget is an intentional hard stop. A provider can be configured
    | and health-checked without allowing a billable generation request.
    |
    */
    'max_variations_per_recipe' => (int) env('AI_IMAGE_MAX_VARIATIONS_PER_RECIPE', 2),
    'max_total_variations' => (int) env('AI_IMAGE_MAX_TOTAL_VARIATIONS', 20),
    'daily_budget_cents' => (int) env('AI_IMAGE_DAILY_BUDGET_CENTS', 0),
    'pilot_cost_estimate_cents_per_image' => (int) env('AI_IMAGE_ESTIMATE_CENTS_PER_IMAGE', 0),
    'max_response_payload_bytes' => (int) env('AI_IMAGE_MAX_RESPONSE_PAYLOAD_BYTES', 12 * 1024 * 1024),
    'production_budget' => [
        'daily_usd' => env('RECIPE_IMAGE_DAILY_BUDGET_USD'),
        'monthly_usd' => env('RECIPE_IMAGE_MONTHLY_BUDGET_USD'),
        'max_cost_per_image_usd' => env('RECIPE_IMAGE_MAX_COST_PER_IMAGE_USD'),
        'batch_size' => max(1, min(5, (int) env('RECIPE_IMAGE_BATCH_SIZE', 5))),
    ],
    'diagnostic_recovery' => [
        'max_calls' => (int) env('AI_IMAGE_DIAGNOSTIC_MAX_CALLS', 1),
        'max_variations' => (int) env('AI_IMAGE_DIAGNOSTIC_MAX_VARIATIONS', 1),
        'budget_cents' => (int) env('AI_IMAGE_DIAGNOSTIC_BUDGET_CENTS', 10),
    ],
    'fallback_provider_comparison' => [
        'max_total_requests' => (int) env('AI_IMAGE_FALLBACK_MAX_TOTAL_REQUESTS', 2),
        'max_requests_per_provider' => (int) env('AI_IMAGE_FALLBACK_MAX_REQUESTS_PER_PROVIDER', 1),
        'budget_cents' => (int) env('AI_IMAGE_FALLBACK_BUDGET_CENTS', 20),
        'bfl_estimate_cents' => (int) env('BFL_IMAGE_ESTIMATE_CENTS', 3),
        'stability_estimate_cents' => (int) env('STABILITY_IMAGE_ESTIMATE_CENTS', 10),
    ],
    'prompt_version' => 'house-chef-v1',
    'providers' => [
        'edenai' => [
            'enabled' => (bool) env('EDEN_AI_ENABLED', false),
            'key' => env('EDEN_AI_API_KEY'),
            'endpoint' => env('EDEN_AI_BASE_URL', 'https://api.edenai.run/v3'),
            'model' => env('EDEN_AI_IMAGE_MODEL'),
        ],
        'pexels' => [
            'enabled' => (bool) env('PEXELS_ENABLED', false),
            'key' => env('PEXELS_API_KEY'),
            'endpoint' => env('PEXELS_BASE_URL', 'https://api.pexels.com'),
        ],
        'gemini' => [
            'enabled' => (bool) env('GEMINI_IMAGE_ENABLED', false),
            'key' => env('GEMINI_API_KEY'),
            'model' => env('GEMINI_IMAGE_MODEL', 'imagen-4.0-generate-001'),
            'endpoint' => env('GEMINI_IMAGE_ENDPOINT', 'https://generativelanguage.googleapis.com/v1beta'),
            'pilot_status' => env('GEMINI_IMAGE_PILOT_STATUS', 'unavailable_for_pilot'),
        ],
        'openai' => [
            'enabled' => (bool) env('OPENAI_IMAGE_ENABLED', false),
            'key' => env('OPENAI_API_KEY'),
            'model' => env('OPENAI_IMAGE_MODEL', 'gpt-image-1.5'),
        ],
        'stability' => [
            'enabled' => (bool) env('STABILITY_IMAGE_ENABLED', false),
            'key' => env('STABILITY_API_KEY'),
            'model' => env('STABILITY_IMAGE_MODEL', 'stable-image-core'),
            'endpoint' => env('STABILITY_IMAGE_BASE_URL', 'https://api.stability.ai'),
        ],
        'bfl' => [
            'enabled' => (bool) env('BFL_IMAGE_ENABLED', false),
            'key' => env('BFL_API_KEY'),
            'model' => env('BFL_IMAGE_MODEL', 'flux-2-pro'),
            'endpoint' => env('BFL_IMAGE_BASE_URL', 'https://api.bfl.ai'),
            'poll_limit' => (int) env('BFL_IMAGE_POLL_LIMIT', 10),
            'poll_interval_ms' => (int) env('BFL_IMAGE_POLL_INTERVAL_MS', 500),
        ],
        'comfyui' => [
            'enabled' => (bool) env('COMFYUI_ENABLED', env('COMFYUI_IMAGE_ENABLED', false)),
            'endpoint' => env('COMFYUI_BASE_URL') ?: (env('COMFYUI_IMAGE_ENDPOINT') ?: 'http://127.0.0.1:8188'),
            'workflow_path' => env('COMFYUI_WORKFLOW_PATH') ?: resource_path('workflows/comfyui/flux1-schnell-api.json'),
            'model' => env('COMFYUI_MODEL', 'flux1-schnell'),
            'timeout' => (int) env('COMFYUI_TIMEOUT', 900),
            'max_concurrent_jobs' => max(1, (int) env('COMFYUI_MAX_CONCURRENT_JOBS', 1)),
            'auto_publish' => (bool) env('RECIPE_IMAGE_AUTO_PUBLISH', false),
            'poll_interval_ms' => max(250, (int) env('COMFYUI_POLL_INTERVAL_MS', 2000)),
            'max_output_bytes' => (int) env('COMFYUI_MAX_OUTPUT_BYTES', 12 * 1024 * 1024),
        ],
        'diffusers' => [
            'enabled' => (bool) env('DIFFUSERS_IMAGE_ENABLED', false),
            'endpoint' => env('DIFFUSERS_IMAGE_ENDPOINT'),
        ],
    ],
    'primary_provider' => env('RECIPE_IMAGE_PRIMARY_PROVIDER', 'bfl'),
    'fallback_provider' => env('RECIPE_IMAGE_FALLBACK_PROVIDER', 'stability'),
];
