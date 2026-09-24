<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

final class ContentTranslation extends Model
{
    protected $fillable = ['locale', 'field', 'value', 'translatable_type', 'translatable_id'];

    public function translatable(): MorphTo
    {
        return $this->morphTo();
    }
}
