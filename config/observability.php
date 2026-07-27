<?php

return [
    'health_cache_seconds' => (int) env('HEALTH_CACHE_SECONDS', 30),
    'low_disk_free_bytes' => (int) env('LOW_DISK_FREE_BYTES', 1073741824),
    'critical_disk_free_bytes' => (int) env('CRITICAL_DISK_FREE_BYTES', 268435456),
    'backup_retention_days' => (int) env('BACKUP_RETENTION_DAYS', 14),
    'backup_minimum_keep' => (int) env('BACKUP_MINIMUM_KEEP', 2),
    'backup_scheduled' => (bool) env('BACKUP_SCHEDULED', false),
];
