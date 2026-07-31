<?php

declare(strict_types=1);

return [
    // Explicitly false in real production. It exists only for isolated test
    // harnesses that exercise the strict contract without a MySQL/PostgreSQL server.
    'allow_sqlite' => (bool) env('ALLOW_SQLITE_PRODUCTION', false),
    'backup_disk' => env('BACKUP_DISK'),
    'trusted_proxies' => env('TRUSTED_PROXIES'),
];
