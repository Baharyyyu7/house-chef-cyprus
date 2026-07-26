<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class NutritionSetting extends Model
{
    protected $fillable = ['key', 'value', 'unit'];
}
