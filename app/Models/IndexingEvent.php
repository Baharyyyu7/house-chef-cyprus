<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class IndexingEvent extends Model
{
    protected $fillable = ['url', 'event_type', 'provider', 'status', 'safe_error_code', 'submitted_at'];

    protected function casts(): array
    {
        return ['submitted_at' => 'datetime'];
    }
}
