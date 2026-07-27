<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class SystemEvent extends Model
{
    protected $fillable = ['event_type', 'severity', 'message', 'correlation_id', 'metadata', 'resolved_at'];

    protected function casts(): array
    {
        return ['metadata' => 'array', 'resolved_at' => 'datetime'];
    }
}
