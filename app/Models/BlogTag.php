<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class BlogTag extends Model
{
    use HasPublicId;

    protected $fillable = ['name', 'slug'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** @return BelongsToMany<BlogPost, $this> */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(BlogPost::class);
    }
}
