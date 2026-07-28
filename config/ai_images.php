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
    'prompt_version' => 'house-chef-v1',
    'providers' => [
        'gemini' => [
            'enabled' => (bool) env('GEMINI_IMAGE_ENABLED', false),
            'key' => env('GEMINI_API_KEY'),
            'model' => env('GEMINI_IMAGE_MODEL', 'imagen-4.0-generate-001'),
            'endpoint' => env('GEMINI_IMAGE_ENDPOINT', 'https://generativelanguage.googleapis.com/v1beta'),
        ],
        'openai' => [
            'enabled' => (bool) env('OPENAI_IMAGE_ENABLED', false),
            'key' => env('OPENAI_API_KEY'),
            'model' => env('OPENAI_IMAGE_MODEL', 'gpt-image-1.5'),
        ],
        'stability' => [
            'enabled' => (bool) env('STABILITY_IMAGE_ENABLED', false),
            'key' => env('STABILITY_API_KEY'),
        ],
        'bfl' => [
            'enabled' => (bool) env('BFL_IMAGE_ENABLED', false),
            'key' => env('BFL_API_KEY'),
        ],
        'comfyui' => [
            'enabled' => (bool) env('COMFYUI_IMAGE_ENABLED', false),
            'endpoint' => env('COMFYUI_IMAGE_ENDPOINT'),
        ],
        'diffusers' => [
            'enabled' => (bool) env('DIFFUSERS_IMAGE_ENABLED', false),
            'endpoint' => env('DIFFUSERS_IMAGE_ENDPOINT'),
        ],
    ],
];
