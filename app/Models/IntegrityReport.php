<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class IntegrityReport extends Model
{
    protected $fillable = ['guard', 'status', 'headline', 'payload'];

    protected function casts(): array
    {
        return ['payload' => 'array', 'headline' => 'integer'];
    }
}
