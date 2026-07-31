<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

final class SeoMetadata extends Model
{
    protected $table = 'seo_metadata';

    protected $fillable = ['meta_title', 'meta_description', 'canonical_override', 'robots', 'og_title', 'og_description', 'og_media_public_id', 'x_title', 'x_description', 'x_media_public_id', 'focus_topic', 'primary_query', 'search_intent', 'index_status', 'schema_status', 'quality_score', 'last_audited_at', 'last_refreshed_at', 'reviewed_by', 'reviewed_at'];

    protected function casts(): array
    {
        return ['last_audited_at' => 'datetime', 'last_refreshed_at' => 'datetime', 'reviewed_at' => 'datetime'];
    }

    /** @return MorphTo<Model, $this> */
    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }
}
