<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class SystemMetric extends Model
{
    protected $fillable = ['metric', 'dimension', 'value', 'recorded_at'];

    protected function casts(): array
    {
        return ['value' => 'float', 'recorded_at' => 'datetime'];
    }
}
