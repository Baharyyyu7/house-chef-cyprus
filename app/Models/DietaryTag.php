<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;

class DietaryTag extends Model
{
    use HasPublicId;

    protected $fillable = ['public_id', 'code', 'name', 'description', 'rule_type', 'threshold_config', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'threshold_config' => 'array'];
    }
}
