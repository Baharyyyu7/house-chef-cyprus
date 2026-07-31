<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class KeywordTopic extends Model
{
    use HasPublicId;

    protected $fillable = ['primary_keyword', 'normalized_keyword', 'locale', 'country', 'intent', 'topic_cluster', 'parent_topic_id', 'priority', 'difficulty_source', 'volume_source', 'trend_direction', 'data_confidence', 'status'];

    /** @return BelongsTo<self, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_topic_id');
    }
}
