<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;

final class ContentOpportunity extends Model
{
    use HasPublicId;

    protected $fillable = ['kind', 'title', 'normalized_key', 'search_intent', 'topic_cluster', 'evidence', 'related_public_ids', 'risk_level', 'priority', 'status', 'reviewed_at'];

    protected function casts(): array
    {
        return ['evidence' => 'array', 'related_public_ids' => 'array', 'reviewed_at' => 'datetime'];
    }
}
