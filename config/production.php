<?php

declare(strict_types=1);

return [
    'release_states' => ['development', 'local_qa', 'staging_ready', 'staging', 'production_ready', 'production', 'maintenance'],
    'readiness_rule_version' => 'production-readiness-v1',
    'monitoring' => [
        'provider_status' => env('MONITORING_PROVIDER_ENABLED', false) ? 'configured' : 'connection_pending',
        'fake_metrics_allowed' => false,
    ],
    'editorial' => [
        'auto_publish' => false,
        'human_approval_required' => true,
        'branded_fallback_is_real_media' => false,
    ],
];
