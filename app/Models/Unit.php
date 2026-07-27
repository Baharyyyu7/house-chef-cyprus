<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = ['public_id', 'code', 'name', 'singular_name', 'plural_name', 'symbol', 'short_label', 'unit_type', 'base_unit_id', 'conversion_factor', 'precision', 'supports_fractions', 'is_metric', 'is_active', 'is_user_visible', 'is_canonical', 'sort_order'];

    protected function casts(): array
    {
        return ['conversion_factor' => 'decimal:8', 'precision' => 'integer', 'supports_fractions' => 'boolean', 'is_metric' => 'boolean', 'is_active' => 'boolean', 'is_user_visible' => 'boolean', 'is_canonical' => 'boolean', 'sort_order' => 'integer'];
    }
}
