<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

/**
 * @property string $public_id
 * @property array<string, string>|null $thumbnail_paths
 */
class Media extends Model
{
    use HasPublicId;

    protected $fillable = ['kind', 'disk', 'path', 'embed_url', 'mime_type', 'size', 'sort_order', 'visibility', 'alt_text', 'width', 'height', 'processing_status', 'thumbnail_status', 'thumbnail_paths', 'processing_error_code', 'processing_error_message', 'processed_at', 'source_name', 'source_url', 'license', 'attribution', 'verification_status', 'confidence', 'reviewed_at', 'is_placeholder', 'generated_by_ai', 'ai_provider', 'ai_model', 'ai_prompt_version', 'ai_review_status', 'focal_point_x', 'focal_point_y', 'checksum_sha256', 'perceptual_hash', 'approved_by_user_id', 'approved_at', 'provider_metadata'];

    protected function casts(): array
    {
        return ['thumbnail_paths' => 'array', 'processed_at' => 'datetime', 'reviewed_at' => 'datetime', 'is_placeholder' => 'boolean', 'generated_by_ai' => 'boolean', 'approved_at' => 'datetime', 'provider_metadata' => 'array'];
    }

    /** @return MorphTo<Model, $this> */
    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return BelongsTo<User, $this> */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function publicUrl(): ?string
    {
        if ($this->visibility !== 'public') {
            return null;
        }

        if ($this->kind === 'external_hotlinked_media') {
            return $this->verification_status === 'verified' && $this->safeUnsplashImageUrl($this->source_url) ? $this->source_url : null;
        }

        if (! is_string($this->path) || $this->path === '') {
            return null;
        }

        return $this->disk === 'public' ? Storage::disk('public')->url($this->path) : asset($this->path);
    }

    public function isApprovedUnsplash(): bool
    {
        return $this->kind === 'external_hotlinked_media'
            && $this->source_name === 'Unsplash'
            && $this->verification_status === 'verified'
            && $this->publicUrl() !== null;
    }

    public function responsiveUrl(int $width): ?string
    {
        $url = $this->publicUrl();
        if ($url === null || ! $this->isApprovedUnsplash()) {
            return $url;
        }

        $separator = str_contains($url, '?') ? '&' : '?';

        return $url.$separator.http_build_query(['auto' => 'format', 'fit' => 'max', 'fm' => 'jpg', 'q' => 80, 'w' => max(320, min(2400, $width))]);
    }

    /** @return array{photographer:string,photographer_url:?string,unsplash_url:?string}|null */
    public function unsplashAttribution(): ?array
    {
        if (! $this->isApprovedUnsplash()) {
            return null;
        }
        $metadata = (array) $this->provider_metadata;

        $photographer = data_get($metadata, 'photographer');
        $photographerUrl = data_get($metadata, 'photographer_url');
        $unsplashUrl = data_get($metadata, 'unsplash_page_url');

        return [
            'photographer' => is_string($photographer) && $photographer !== '' ? $photographer : 'Unsplash',
            'photographer_url' => $this->safeUnsplashPageUrl($photographerUrl) && is_string($photographerUrl) ? $photographerUrl : null,
            'unsplash_url' => $this->safeUnsplashPageUrl($unsplashUrl) && is_string($unsplashUrl) ? $unsplashUrl : null,
        ];
    }

    private function safeUnsplashImageUrl(mixed $url): bool
    {
        $parts = is_string($url) ? parse_url($url) : false;

        return is_array($parts) && ($parts['scheme'] ?? null) === 'https' && strtolower((string) ($parts['host'] ?? '')) === 'images.unsplash.com' && ! isset($parts['user'], $parts['pass']) && (! isset($parts['port']) || (int) $parts['port'] === 443);
    }

    private function safeUnsplashPageUrl(mixed $url): bool
    {
        $parts = is_string($url) ? parse_url($url) : false;

        return is_array($parts) && ($parts['scheme'] ?? null) === 'https' && in_array(strtolower((string) ($parts['host'] ?? '')), ['unsplash.com', 'images.unsplash.com'], true) && ! isset($parts['user'], $parts['pass']) && (! isset($parts['port']) || (int) $parts['port'] === 443);
    }
}
