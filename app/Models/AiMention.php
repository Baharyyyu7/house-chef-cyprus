<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;

final class AiMention extends Model
{
    use HasPublicId;

    protected $fillable = [
        'engine',
        'mode',
        'prompt',
        'answer_body',
        'mentioned',
        'brand_context',
        'linked',
        'linked_url',
        'cited_sources',
        'competitor_mentions',
        'status',
        'notes',
        'checked_at',
    ];

    protected function casts(): array
    {
        return [
            'mentioned' => 'boolean',
            'linked' => 'boolean',
            'cited_sources' => 'array',
            'competitor_mentions' => 'array',
            'checked_at' => 'datetime',
        ];
    }

    public function scopeMentioned($query)
    {
        return $query->where('mentioned', true);
    }

    public function scopeUnreviewed($query)
    {
        return $query->where('status', 'new');
    }
}
