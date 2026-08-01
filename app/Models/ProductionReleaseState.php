<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class ProductionReleaseState extends Model
{
    public const STATES = ['development', 'local_qa', 'staging_ready', 'staging', 'production_ready', 'production', 'maintenance'];

    protected $fillable = ['state', 'last_ci_commit', 'last_ci_at', 'last_smoke_at', 'last_backup_at', 'last_migration_at', 'last_content_health_scan_at', 'blockers'];

    protected function casts(): array
    {
        return [
            'last_ci_at' => 'datetime', 'last_smoke_at' => 'datetime', 'last_backup_at' => 'datetime',
            'last_migration_at' => 'datetime', 'last_content_health_scan_at' => 'datetime', 'blockers' => 'array',
        ];
    }
}
