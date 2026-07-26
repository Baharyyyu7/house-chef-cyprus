<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class DataQualityIssue extends Model
{
    protected $fillable = ['issue_type', 'severity', 'entity_type', 'entity_public_id', 'message', 'detected_at', 'first_detected_at', 'last_detected_at', 'resolved_at', 'resolution_status', 'metadata'];

    protected function casts(): array
    {
        return ['detected_at' => 'datetime', 'first_detected_at' => 'datetime', 'last_detected_at' => 'datetime', 'resolved_at' => 'datetime', 'metadata' => 'array'];
    }
}
