<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class AudienceCountryDay extends Model
{
    protected $fillable = ['day', 'country_code', 'hits'];

    protected $casts = [
        'day' => 'date',
        'hits' => 'integer',
    ];
}
