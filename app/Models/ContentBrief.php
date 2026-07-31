<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ContentBrief extends Model
{
    use HasPublicId;

    protected $fillable = ['keyword_topic_id', 'proposed_title', 'target_audience', 'search_intent', 'user_question', 'direct_answer', 'required_sections', 'required_facts', 'related_recipe_public_ids', 'internal_links', 'prohibited_claims', 'source_notes', 'freshness_requirement', 'risk_level', 'status', 'created_by'];

    protected function casts(): array
    {
        return ['required_sections' => 'array', 'required_facts' => 'array', 'related_recipe_public_ids' => 'array', 'internal_links' => 'array', 'prohibited_claims' => 'array', 'source_notes' => 'array'];
    }

    /** @return BelongsTo<KeywordTopic, $this> */
    public function topic(): BelongsTo
    {
        return $this->belongsTo(KeywordTopic::class, 'keyword_topic_id');
    }
}
