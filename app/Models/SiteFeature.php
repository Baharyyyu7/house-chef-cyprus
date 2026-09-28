<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class SiteFeature extends Model
{
    protected $fillable = ['key', 'enabled', 'updated_by_user_id'];

    protected function casts(): array
    {
        return ['enabled' => 'boolean'];
    }
}
