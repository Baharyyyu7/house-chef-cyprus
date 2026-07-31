<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class SocialDistributionDraft extends Model
{
    use HasPublicId;

    protected $fillable = ['blog_post_id', 'channel', 'body', 'safe_metadata', 'status', 'approved_at', 'published_at'];

    protected function casts(): array
    {
        return ['safe_metadata' => 'array', 'approved_at' => 'datetime', 'published_at' => 'datetime'];
    }

    /** @return BelongsTo<BlogPost, $this> */
    public function post(): BelongsTo
    {
        return $this->belongsTo(BlogPost::class, 'blog_post_id');
    }
}
