<?php

declare(strict_types=1);

return [
    /* All values are intentionally conservative and shared-hosting friendly. */
    'allowed_remote_hosts' => [
        'images.pexels.com',
        'images.unsplash.com',
    ],
    'max_redirects' => 2,
    'max_download_bytes' => (int) env('MEDIA_MAX_DOWNLOAD_BYTES', 12 * 1024 * 1024),
    'batch_limit' => max(1, min(100, (int) env('MEDIA_OPERATION_BATCH_LIMIT', 25))),
    'health_rule_version' => 'media-excellence-v1',
    'quality' => [
        'webp' => (int) env('MEDIA_WEBP_QUALITY', 84),
        'jpeg' => (int) env('MEDIA_JPEG_QUALITY', 86),
        'avif' => (int) env('MEDIA_AVIF_QUALITY', 55),
    ],
    'responsive_widths' => [400, 640, 800, 1200, 1600],
    'recipe_profiles' => [
        'thumbnail' => [400, 300],
        'square' => [640, 640],
        'card' => [800, 600],
        'open_graph' => [1200, 630],
        'hero' => [1600, 1000],
    ],
    'blog_profiles' => [
        'hero' => [1600, 900],
        'open_graph' => [1200, 630],
        'card' => [800, 600],
        'square' => [1080, 1080],
        'portrait' => [1080, 1350],
        'thumbnail' => [400, 300],
    ],
    'cdn_base_url' => rtrim((string) env('MEDIA_CDN_BASE_URL', ''), '/'),
    'approval' => [
        'media_reviewer_can_final_approve' => (bool) env('MEDIA_REVIEWER_CAN_FINAL_APPROVE', false),
    ],
];
