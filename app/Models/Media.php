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

    protected $fillable = ['kind', 'disk', 'path', 'embed_url', 'mime_type', 'size', 'sort_order', 'visibility', 'media_status', 'fidelity_status', 'fidelity_confidence', 'fidelity_reasons', 'license_review_status', 'attribution_review_status', 'optimization_status', 'responsive_variants', 'media_reviewed_at', 'media_reviewer_user_id', 'uploaded_by_user_id', 'license_reviewed_at', 'attribution_reviewed_at', 'health_scanned_at', 'health_rule_version', 'alt_text', 'caption', 'width', 'height', 'processing_status', 'thumbnail_status', 'thumbnail_paths', 'processing_error_code', 'processing_error_message', 'processed_at', 'source_name', 'source_url', 'license', 'attribution', 'verification_status', 'confidence', 'reviewed_at', 'is_placeholder', 'generated_by_ai', 'ai_provider', 'ai_model', 'ai_prompt_version', 'ai_review_status', 'focal_point_x', 'focal_point_y', 'checksum_sha256', 'perceptual_hash', 'approved_by_user_id', 'approved_at', 'provider_metadata'];

    protected function casts(): array
    {
        return ['thumbnail_paths' => 'array', 'responsive_variants' => 'array', 'fidelity_reasons' => 'array', 'processed_at' => 'datetime', 'reviewed_at' => 'datetime', 'media_reviewed_at' => 'datetime', 'license_reviewed_at' => 'datetime', 'attribution_reviewed_at' => 'datetime', 'health_scanned_at' => 'datetime', 'is_placeholder' => 'boolean', 'generated_by_ai' => 'boolean', 'approved_at' => 'datetime', 'provider_metadata' => 'array'];
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

    /** @return BelongsTo<User, $this> */
    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'media_reviewer_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
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

        if ($this->disk === 'public') {
            $cdnBaseUrl = (string) config('media_excellence.cdn_base_url');

            return $cdnBaseUrl !== '' ? $cdnBaseUrl.'/'.ltrim($this->path, '/') : Storage::disk('public')->url($this->path);
        }

        return asset($this->path);
    }

    public function isApprovedUnsplash(): bool
    {
        return $this->kind === 'external_hotlinked_media'
            && $this->source_name === 'Unsplash'
            && $this->verification_status === 'verified'
            && $this->publicUrl() !== null;
    }

    public function isApprovedRealMedia(): bool
    {
        return $this->visibility === 'public'
            && ! $this->is_placeholder
            && $this->verification_status === 'verified'
            && $this->media_status === 'approved_real_media'
            && $this->license_review_status === 'approved'
            && $this->attribution_review_status === 'approved';
    }

    public function responsiveUrl(int $width, string $format = 'jpeg'): ?string
    {
        $url = $this->publicUrl();
        if ($url === null) {
            return $url;
        }

        if ($this->isApprovedRealMedia() && $this->disk === 'public') {
            $selectedPath = null;
            $selectedWidth = PHP_INT_MAX;
            foreach ($this->responsiveVariants() as $variant) {
                $path = $variant[$format] ?? null;
                $variantWidth = $variant['width'] ?? null;
                if (! is_string($path) || ! is_int($variantWidth) || $variantWidth < $width || $variantWidth >= $selectedWidth) {
                    continue;
                }
                $selectedPath = $path;
                $selectedWidth = $variantWidth;
            }

            if ($selectedPath !== null) {
                return Storage::disk('public')->url($selectedPath);
            }
        }

        if (! $this->isApprovedUnsplash()) {
            return $url;
        }

        $separator = str_contains($url, '?') ? '&' : '?';

        return $url.$separator.http_build_query(['auto' => 'format', 'fit' => 'max', 'fm' => 'jpg', 'q' => 80, 'w' => max(320, min(2400, $width))]);
    }

    /** @param list<int> $widths */
    public function responsiveSrcset(array $widths, string $format = 'jpeg'): ?string
    {
        if (! $this->isApprovedRealMedia()) {
            return null;
        }

        $items = collect($widths)->map(function (int $width) use ($format): ?string {
            $url = $this->responsiveUrl($width, $format);

            return $url === null ? null : $url.' '.$width.'w';
        })->filter()->unique()->values();

        return $items->isEmpty() ? null : $items->implode(', ');
    }

    /**
     * Paylaşım ve zengin sonuç görseli.
     *
     * og:image, twitter:image ve tarif şeması ham kaynağı gösteriyordu -
     * bir tarifte 2,77 MB'lık PNG. O dosyayı WhatsApp, Facebook ve X her
     * paylaşım önizlemesinde yeniden çekiyor, Google zengin sonuç için
     * okuyor. Hattın ürettiği open_graph varyantı (1200x630) tam bu iş
     * için var ve kullanılmıyordu.
     *
     * Biçim bilerek jpeg: paylaşım tarayıcılarının bir kısmı avif ve webp
     * okumuyor, okumadığında da önizleme boş çıkıyor.
     *
     * @return array{url:string,width:?int,height:?int}|null
     */
    public function socialImage(): ?array
    {
        $url = $this->publicUrl();

        if ($url === null) {
            return null;
        }

        if ($this->isApprovedRealMedia() && $this->disk === 'public') {
            foreach ($this->responsiveVariants() as $variant) {
                if (($variant['name'] ?? null) !== 'open_graph') {
                    continue;
                }

                $path = $variant['jpeg'] ?? null;

                if (is_string($path) && $path !== '') {
                    return [
                        'url' => Storage::disk('public')->url($path),
                        'width' => $variant['width'] ?? null,
                        'height' => $variant['height'] ?? null,
                    ];
                }
            }
        }

        return ['url' => $url, 'width' => $this->width, 'height' => $this->height];
    }

    /** @return list<array{name?:string,jpeg?:string,width?:int,height?:int,webp?:string,avif?:string}> */
    private function responsiveVariants(): array
    {
        $raw = $this->getRawOriginal('responsive_variants');
        if (! is_string($raw) || $raw === '') {
            return [];
        }
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? array_values(array_filter($decoded, is_array(...))) : [];
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
