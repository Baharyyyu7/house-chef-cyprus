<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ContentRevision extends Model
{
    protected $fillable = ['blog_post_id', 'revision_number', 'reason', 'previous_body', 'proposed_body', 'changed_seo_fields', 'created_by', 'reviewed_at'];

    protected function casts(): array
    {
        return ['changed_seo_fields' => 'array', 'reviewed_at' => 'datetime'];
    }

    /** @return BelongsTo<BlogPost, $this> */
    public function post(): BelongsTo
    {
        return $this->belongsTo(BlogPost::class, 'blog_post_id');
    }
}
