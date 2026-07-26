<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataSource extends Model
{
    protected $fillable = ['name', 'base_url', 'type', 'license', 'reliability_score', 'last_success_at', 'is_active'];

    protected function casts(): array
    {
        return ['last_success_at' => 'datetime', 'is_active' => 'boolean'];
    }
}
