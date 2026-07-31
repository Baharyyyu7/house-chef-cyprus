<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;

final class ContentTopicCluster extends Model
{
    use HasPublicId;

    protected $fillable = ['name', 'slug', 'search_intent', 'pillar_title', 'review_interval', 'internal_link_plan'];

    protected function casts(): array
    {
        return ['internal_link_plan' => 'array'];
    }
}
