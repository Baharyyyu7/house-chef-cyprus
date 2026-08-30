<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class ZeroResultSearch extends Model
{
    protected $fillable = ['normalized_query', 'locale', 'search_count', 'last_searched_at', 'suggested_action', 'resolved_at', 'resolved_result_count'];

    protected function casts(): array
    {
        return ['last_searched_at' => 'datetime', 'resolved_at' => 'datetime'];
    }
}
