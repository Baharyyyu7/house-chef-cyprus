<?php

declare(strict_types=1);

return [
    'release_states' => ['development', 'local_qa', 'staging_ready', 'staging', 'production_ready', 'production', 'maintenance'],
    'readiness_rule_version' => 'production-readiness-v1',
    'release_state' => env('APP_RELEASE_STATE', 'development'),
    'canonical_host' => env('CANONICAL_HOST'),
    'canonical_scheme' => env('CANONICAL_SCHEME', 'https'),
    'www_redirect_policy' => env('WWW_REDIRECT_POLICY', 'to_apex'),
    'backup_disk' => env('BACKUP_DISK'),
    'public_storage_url' => env('PUBLIC_STORAGE_URL'),
    'trusted_proxies' => env('TRUSTED_PROXIES'),
    'allow_sqlite' => (bool) env('PRODUCTION_ALLOW_SQLITE', false),
    'artifact_version' => 'production-artifact-v1',
    'commit_sha' => env('APP_COMMIT_SHA'),
    'mail_health_requires_smtp' => (bool) env('PRODUCTION_MAIL_REQUIRED', true),
    'scheduler_max_age_minutes' => (int) env('SCHEDULER_MAX_AGE_MINUTES', 30),
    'queue_worker_mode' => env('QUEUE_WORKER_MODE', 'cron_stop_when_empty'),
    'analytics' => [
        'ga_measurement_id' => env('GA_MEASUREMENT_ID'),
        'search_console_verification' => env('SEARCH_CONSOLE_VERIFICATION'),
        'bing_verification' => env('BING_VERIFICATION'),
    ],
    'monitoring' => [
        'provider_status' => env('MONITORING_PROVIDER_ENABLED', false) ? 'configured' : 'connection_pending',
        'fake_metrics_allowed' => false,
    ],
    // Ülke sayacı önbellekte birikir ve en fazla bu aralıkla tabloya yazılır
    // (App\Services\Geo\AudienceCountryBuffer). 0: her isabet hemen yazılır.
    'audience_flush_seconds' => (int) env('AUDIENCE_FLUSH_SECONDS', 60),
    'editorial' => [
        'auto_publish' => false,
        'human_approval_required' => true,
        'branded_fallback_is_real_media' => false,
        'publication_guard' => env('PUBLICATION_GUARD'),
        'daily_publication_cap' => (int) env('DAILY_PUBLICATION_CAP', 10),
    ],
];
