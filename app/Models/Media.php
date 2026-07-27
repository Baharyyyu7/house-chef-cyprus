<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property string $public_id
 * @property array<string, string>|null $thumbnail_paths
 */
class Media extends Model
{
    use HasPublicId;

    protected $fillable = ['kind', 'disk', 'path', 'embed_url', 'mime_type', 'size', 'sort_order', 'visibility', 'alt_text', 'width', 'height', 'processing_status', 'thumbnail_status', 'thumbnail_paths', 'processing_error_code', 'processing_error_message', 'processed_at'];

    protected function casts(): array
    {
        return ['thumbnail_paths' => 'array', 'processed_at' => 'datetime'];
    }

    /** @return MorphTo<Model, $this> */
    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }
}
