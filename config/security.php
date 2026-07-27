<?php

return [
    'cors_origins' => array_filter(explode(',', (string) env('CORS_ALLOWED_ORIGINS', 'http://127.0.0.1:8010,http://localhost:8010'))),
    'max_upload_bytes' => (int) env('MEDIA_MAX_UPLOAD_BYTES', 10 * 1024 * 1024),
    'max_image_pixels' => (int) env('MEDIA_MAX_IMAGE_PIXELS', 24_000_000),
    'allowed_media_mimes' => ['image/jpeg', 'image/png', 'image/webp'],
];
