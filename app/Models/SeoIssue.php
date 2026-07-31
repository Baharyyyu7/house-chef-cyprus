<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class SeoIssue extends Model
{
    protected $fillable = ['fingerprint', 'category', 'entity_type', 'entity_public_id', 'url', 'severity', 'message', 'safe_metadata', 'first_detected_at', 'last_detected_at', 'resolved_at', 'reopened_at'];

    protected function casts(): array
    {
        return ['safe_metadata' => 'array', 'first_detected_at' => 'datetime', 'last_detected_at' => 'datetime', 'resolved_at' => 'datetime', 'reopened_at' => 'datetime'];
    }
}
