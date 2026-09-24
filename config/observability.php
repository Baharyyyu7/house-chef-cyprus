<?php

return [
    'health_cache_seconds' => (int) env('HEALTH_CACHE_SECONDS', 30),
    'low_disk_free_bytes' => (int) env('LOW_DISK_FREE_BYTES', 1073741824),
    'critical_disk_free_bytes' => (int) env('CRITICAL_DISK_FREE_BYTES', 268435456),
    'backup_retention_days' => (int) env('BACKUP_RETENTION_DAYS', 14),
    'backup_minimum_keep' => (int) env('BACKUP_MINIMUM_KEEP', 2),
    'backup_scheduled' => (bool) env('BACKUP_SCHEDULED', false),
    'backup_disk' => env('BACKUP_DISK', 'local'),
    'backup_path' => env('BACKUP_PATH', 'backups'),
    'mysqldump_binary' => env('MYSQLDUMP_BINARY'),
    'mysql_binary' => env('MYSQL_BINARY'),
    'backup_dump_timeout_seconds' => (int) env('BACKUP_DUMP_TIMEOUT_SECONDS', 900),
    // Doğrulayıcının dökümde `migrations` tablosunu arayacağı en fazla bayt.
    // 32 MiB'ti; tablolar alfabetik döküldüğü için `migrations` veritabanı
    // büyüdükçe ileri kayıyor. 14 Eylül 2026'da 35,9 MB'a düştü, sağlam
    // yedek "geçersiz" sayıldı ve deploy durdu. Sınır zaman korumasıdır,
    // dökümün tamamını kapsayacak kadar geniş olmalı.
    'backup_verify_max_sql_bytes' => (int) env('BACKUP_VERIFY_MAX_SQL_BYTES', 1073741824),
];
