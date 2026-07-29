<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class BlogCategory extends Model
{
    use HasPublicId;

    protected $fillable = ['name', 'slug', 'description'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** @return HasMany<BlogPost, $this> */
    public function posts(): HasMany
    {
        return $this->hasMany(BlogPost::class);
    }
}
