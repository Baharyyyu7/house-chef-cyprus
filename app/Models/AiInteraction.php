<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class AiInteraction extends Model
{
    use HasPublicId;

    protected $fillable = ['user_id', 'ai_conversation_id', 'purpose', 'provider', 'model', 'prompt_version', 'request_fingerprint', 'safe_request_summary', 'safe_response_summary', 'response_payload', 'input_tokens', 'output_tokens', 'cost_cents', 'latency_ms', 'cache_hit', 'fallback_used', 'status', 'safe_error_code', 'safety_events', 'completed_at'];

    protected function casts(): array
    {
        return ['response_payload' => 'array', 'safety_events' => 'array', 'cache_hit' => 'boolean', 'fallback_used' => 'boolean', 'completed_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AiConversation::class, 'ai_conversation_id');
    }
}
