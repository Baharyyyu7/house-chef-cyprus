<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Models\AuditLog;
use App\Models\BlogPost;
use App\Models\Media;
use App\Models\Recipe;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Imports an operator-approved, private local image only after all media gates
 * have passed. The service deliberately has no provider call: it cannot invent
 * a source, approval or provenance record.
 */
final class ApprovedMediaImportService
{
    private const SOURCE_ROOT = 'media-import';

    private const ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    public function __construct(private readonly MediaOptimizationService $optimizer) {}

    /** @param array<string,mixed> $entry @return array<string,mixed> */
    public function preview(array $entry, bool $allowControlledResample, float $maxUpscale): array
    {
        $contentType = (string) ($entry['content_type'] ?? 'recipe');
        $claimedPublicId = (string) ($entry['public_id'] ?? '');
        $sourceFilename = (string) ($entry['source_filename'] ?? '');
        $model = $this->content($contentType, $claimedPublicId, $sourceFilename);
        $path = $this->sourcePath($contentType, $sourceFilename, $model->slug);
        $bytes = file_get_contents($path);
        $image = is_string($bytes) ? @getimagesizefromstring($bytes) : false;
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = is_string($bytes) && $finfo !== false ? finfo_buffer($finfo, $bytes) : false;
        if ($finfo !== false) {
            finfo_close($finfo);
        }

        if (! is_array($image) || ! is_string($mime) || ! in_array($mime, self::ALLOWED_MIME_TYPES, true)) {
            throw new \RuntimeException('MEDIA_SOURCE_DECODE_OR_MIME_FAILED');
        }
        $width = (int) $image[0];
        $height = (int) $image[1];
        $megapixels = ($width * $height) / 1000000;
        if ($width < 1400 || $height < 850 || $megapixels < 1.35) {
            throw new \RuntimeException('SOURCE_TOO_SMALL');
        }
        $scale = $this->maximumScale($width, $height, $contentType);
        if (! $allowControlledResample && $scale > 1.0) {
            throw new \RuntimeException('SOURCE_RESAMPLE_REQUIRES_EXPLICIT_ALLOWANCE');
        }
        if ($scale > $maxUpscale) {
            throw new \RuntimeException('SOURCE_UPSCALE_LIMIT_EXCEEDED');
        }
        $this->approval($entry);
        $checksum = hash('sha256', $bytes);
        $duplicate = Media::query()->where('checksum_sha256', $checksum)->first();
        if ($duplicate instanceof Media && ($duplicate->mediable_type !== $model::class || $duplicate->mediable_id !== $model->getKey())) {
            throw new \RuntimeException('MEDIA_EXACT_DUPLICATE_REUSE_REJECTED');
        }

        return [
            'content_type' => $contentType,
            'public_id' => $model->public_id,
            'claimed_public_id' => $claimedPublicId,
            'content_resolution' => $model->public_id === $claimedPublicId ? 'public_id' : 'published_slug_after_public_id_miss',
            'slug' => $model->slug,
            'source_state' => $this->sourceState($width, $height, $scale),
            'source' => ['mime' => $mime, 'width' => $width, 'height' => $height, 'megapixels' => round($megapixels, 2), 'checksum_sha256' => $checksum],
            'maximum_upscale_factor' => round($scale, 4),
            'variants' => $this->variantReport($width, $height, $contentType),
            'already_imported' => $duplicate instanceof Media,
            'mutates_database' => false,
            'public_candidate_path_exposed' => false,
        ];
    }

    /** @param array<string,mixed> $entry @return array<string,mixed> */
    public function import(array $entry, bool $allowControlledResample, float $maxUpscale, bool $replaceFallbacks, bool $publish): array
    {
        $preview = $this->preview($entry, $allowControlledResample, $maxUpscale);
        if (! $publish) {
            return [...$preview, 'status' => 'PREVIEW_ONLY'];
        }
        $contentType = $preview['content_type'];
        $model = $this->content($contentType, (string) $preview['public_id'], (string) $entry['source_filename']);
        $source = $this->sourcePath($contentType, (string) $entry['source_filename'], $model->slug);
        $bytes = file_get_contents($source);
        if (! is_string($bytes)) {
            throw new \RuntimeException('MEDIA_SOURCE_READ_FAILED');
        }
        $extension = match ($preview['source']['mime']) {
            'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', default => throw new \RuntimeException('MEDIA_SOURCE_MIME_FAILED'),
        };
        $storedPaths = [];
        try {
            $result = DB::transaction(function () use ($model, $contentType, $entry, $preview, $bytes, $extension, $replaceFallbacks, &$storedPaths): array {
                $existing = $model->media()->where('checksum_sha256', $preview['source']['checksum_sha256'])->first();
                if ($existing instanceof Media) {
                    return ['media_public_id' => $existing->public_id, 'idempotent' => true];
                }
                $path = 'media/approved-synthetic/'.$contentType.'/'.$model->public_id.'/source.'.$extension;
                Storage::disk('public')->put($path, $bytes, ['visibility' => 'public']);
                $storedPaths[] = $path;
                $now = now();
                $media = $model->media()->create([
                    'public_id' => (string) Str::ulid(), 'kind' => 'recipe_synthetic', 'disk' => 'public', 'path' => $path,
                    'mime_type' => $preview['source']['mime'], 'size' => strlen($bytes), 'sort_order' => 0, 'visibility' => 'public',
                    'media_status' => 'approved_real_media', 'verification_status' => 'verified', 'fidelity_status' => 'strong_match', 'fidelity_confidence' => 100,
                    'fidelity_reasons' => ['Operator-approved exact dish identity match.'], 'license_review_status' => 'approved', 'attribution_review_status' => 'approved',
                    'media_reviewed_at' => $now, 'license_reviewed_at' => $now, 'attribution_reviewed_at' => $now, 'alt_text' => (string) ($entry['alt_text'] ?? $model->title),
                    'caption' => 'Yapay zekâ ile üretilmiş ve editoryal olarak onaylanmış yemek görseli.', 'width' => $preview['source']['width'], 'height' => $preview['source']['height'],
                    'processing_status' => 'pending', 'optimization_status' => 'pending', 'source_name' => 'House Chef Cyprus approved synthetic media',
                    'license' => 'House Chef Cyprus production-approved synthetic media', 'attribution' => 'Sentetik görsel · House Chef Cyprus', 'is_placeholder' => false,
                    'generated_by_ai' => true, 'ai_provider' => (string) data_get($entry, 'provenance.provider', 'built_in_image_generation'),
                    'ai_model' => (string) data_get($entry, 'provenance.model', 'built_in_image_generation'), 'ai_prompt_version' => (string) data_get($entry, 'provenance.prompt_version', 'recipe-media-v1'),
                    'ai_review_status' => 'SYNTHETIC_APPROVED', 'checksum_sha256' => $preview['source']['checksum_sha256'],
                    'approved_at' => $now, 'provider_metadata' => ['synthetic' => true, 'approval_state' => 'SYNTHETIC_APPROVED', 'reviewer_label' => (string) $entry['reviewer'], 'generation_timestamp' => (string) data_get($entry, 'provenance.generated_at'), 'prompt_version' => (string) data_get($entry, 'provenance.prompt_version'), 'source_state' => $preview['source_state']],
                ]);
                $optimization = ['processed' => 0, 'skipped' => 0, 'failed' => 0];
                $this->optimizer->optimizeItem($media, $optimization);
                $media->refresh();
                if ($optimization['failed'] !== 0 || ! $this->hasVariants($media, $contentType)) {
                    throw new \RuntimeException('MEDIA_VARIANT_GENERATION_FAILED');
                }
                /** @var array<string, array<string, string>> $variants */
                $variants = (array) $media->responsive_variants;
                foreach ($variants as $variant) {
                    if (is_array($variant)) {
                        foreach (['jpeg', 'webp', 'avif'] as $format) {
                            if (isset($variant[$format]) && is_string($variant[$format])) {
                                $storedPaths[] = $variant[$format];
                            }
                        }
                    }
                }
                if ($replaceFallbacks) {
                    $model->media()->where('id', '!=', $media->id)->where('media_status', 'branded_fallback')->update(['media_status' => 'superseded', 'sort_order' => 10]);
                }
                if ($model instanceof BlogPost) {
                    $model->update(['hero_media_id' => $media->id]);
                }
                AuditLog::query()->create(['action' => 'media.import_approved', 'auditable_type' => $model::class, 'auditable_id' => $model->id, 'metadata' => ['content_public_id' => $model->public_id, 'media_public_id' => $media->public_id, 'checksum_sha256' => $media->checksum_sha256, 'synthetic' => true, 'auto_publish' => false]]);

                return ['media_public_id' => $media->public_id, 'idempotent' => false, 'variants' => array_keys((array) $media->responsive_variants)];
            });
        } catch (\Throwable $exception) {
            foreach (array_unique($storedPaths) as $path) {
                Storage::disk('public')->delete($path);
            }
            throw $exception;
        }

        return [...$preview, ...$result, 'status' => 'IMPORTED'];
    }

    /** @return Recipe|BlogPost */
    private function content(string $contentType, string $publicId, string $sourceFilename): Model
    {
        $query = match ($contentType) {
            'recipe' => Recipe::query()->where('status', 'published')->where('verification_status', 'verified'),
            'blog' => BlogPost::query()->publiclyVisible(),
            default => throw new \RuntimeException('MEDIA_CONTENT_TYPE_INVALID'),
        };
        $model = (clone $query)->where('public_id', $publicId)->first();
        if (! $model instanceof Model) {
            // A seed may retain an older public ID while the approved source
            // package was assembled elsewhere. The strict filename grammar
            // supplies the canonical slug; this never updates any public ID.
            $model = $query->where('slug', $this->sourceSlug($contentType, $sourceFilename))->first();
        }
        if (! $model instanceof Model) {
            // İçerik var ama yayından çekilmişse bu bir bütünlük ihlali değil,
            // beklenen kayma: manifest sabit, yayın durumu değişken. İkisini
            // ayırmazsak "onaylı görseli olan bir tarifi asla yayından
            // alamazsın" kuralı doğuyor ve deploy duruyor.
            //
            // Güvenlik özelliği aynen duruyor: iki durumda da içe aktarma
            // yapılmıyor, yalnızca deploy'u durdurup durdurmayacağı değişiyor.
            // İçerik yayından çekilmişse ya da tamamen silinmişse içe
            // aktarılacak bir şey yok - bu bir hata değil, boş işlem.
            // Manifest sabit, içerik değişken: kapı bu kaymayı hata sayınca
            // deploy kalıcı olarak bloke oluyordu.
            throw new \RuntimeException(
                $this->contentExistsUnpublished($contentType, $publicId, $sourceFilename)
                    ? 'MEDIA_CONTENT_WITHDRAWN'
                    : 'MEDIA_CONTENT_MISSING'
            );
        }

        return $model;
    }

    /**
     * Kayıt gerçekten var mı, yoksa yalnız yayında mı değil?
     *
     * Yayın filtresi olmadan aranır. Bulunursa içerik duruyor ama yayından
     * çekilmiş demektir; bulunmazsa manifest var olmayan bir kaydı işaret
     * ediyordur ve bu gerçek bir hatadır.
     */
    private function contentExistsUnpublished(string $contentType, string $publicId, string $sourceFilename): bool
    {
        // withTrashed: soft-delete edilmiş kayıt hâlâ vardır, yalnız
        // yayından çekilmiştir.
        $sorgu = match ($contentType) {
            'recipe' => Recipe::withTrashed(),
            'blog' => BlogPost::withTrashed(),
            default => null,
        };

        if ($sorgu === null) {
            return false;
        }

        if ((clone $sorgu)->where('public_id', $publicId)->exists()) {
            return true;
        }

        try {
            $slug = $this->sourceSlug($contentType, $sourceFilename);
        } catch (\RuntimeException) {
            return false;
        }

        return $sorgu->where('slug', $slug)->exists();
    }

    private function sourceSlug(string $contentType, string $filename): string
    {
        if (! preg_match('/\\A(?:recipe|blog)--([a-z0-9-]+)\\.(?:jpe?g|png|webp)\\z/i', $filename, $matches)
            || ! str_starts_with(strtolower($filename), strtolower($contentType).'--')) {
            throw new \RuntimeException('MEDIA_SOURCE_FILENAME_INVALID');
        }

        return $matches[1];
    }

    private function sourcePath(string $contentType, string $filename, string $slug): string
    {
        $this->sourceSlug($contentType, $filename);
        $expected = $contentType.'--'.$slug;
        if (pathinfo($filename, PATHINFO_FILENAME) !== $expected) {
            throw new \RuntimeException('MEDIA_SOURCE_SLUG_MISMATCH');
        }
        $root = realpath(storage_path('app/'.self::SOURCE_ROOT.'/'.$contentType.'s'));
        $candidate = $root === false ? false : realpath($root.DIRECTORY_SEPARATOR.$filename);
        if ($root === false || $candidate === false || ! str_starts_with($candidate, $root.DIRECTORY_SEPARATOR) || is_link($candidate) || ! is_file($candidate)) {
            throw new \RuntimeException('MEDIA_SOURCE_PATH_INVALID');
        }

        return $candidate;
    }

    /** @param array<string,mixed> $entry */
    private function approval(array $entry): void
    {
        if (($entry['identity_match'] ?? false) !== true) {
            throw new \RuntimeException('MEDIA_IDENTITY_APPROVAL_REQUIRED');
        }
        if (($entry['approval_state'] ?? null) !== 'APPROVED' || ! is_string($entry['reviewer'] ?? null) || trim((string) $entry['reviewer']) === '') {
            throw new \RuntimeException('MEDIA_APPROVAL_OR_REVIEWER_REQUIRED');
        }
        $provenance = $entry['provenance'] ?? null;
        if (! is_array($provenance) || ! is_string($provenance['provider'] ?? null) || ! is_string($provenance['generated_at'] ?? null) || ! is_string($provenance['prompt_version'] ?? null)) {
            throw new \RuntimeException('MEDIA_PROVENANCE_REQUIRED');
        }
    }

    private function maximumScale(int $sourceWidth, int $sourceHeight, string $contentType): float
    {
        $profiles = $contentType === 'blog' ? config('media_excellence.blog_profiles') : config('media_excellence.recipe_profiles');
        $maximum = 1.0;
        foreach ((array) $profiles as [$targetWidth, $targetHeight]) {
            // The resample cap is applied before focal cropping. Cropping never
            // stretches pixels; it only chooses an aspect-ratio-safe region.
            $maximum = max($maximum, $targetWidth / max(1, $sourceWidth), $targetHeight / max(1, $sourceHeight));
        }

        return $maximum;
    }

    /** @return array<string,array<string,int|float>> */
    private function variantReport(int $sourceWidth, int $sourceHeight, string $contentType): array
    {
        $profiles = $contentType === 'blog' ? config('media_excellence.blog_profiles') : config('media_excellence.recipe_profiles');
        $report = [];
        foreach ((array) $profiles as $name => [$width, $height]) {
            $report[(string) $name] = ['width' => (int) $width, 'height' => (int) $height, 'upscale_factor' => round($this->scaleFor($sourceWidth, $sourceHeight, (int) $width, (int) $height), 4)];
        }

        return $report;
    }

    private function scaleFor(int $sourceWidth, int $sourceHeight, int $targetWidth, int $targetHeight): float
    {
        return max($targetWidth / max(1, $sourceWidth), $targetHeight / max(1, $sourceHeight));
    }

    private function sourceState(int $width, int $height, float $scale): string
    {
        if ($scale > 1.0 && ($width < 1600 || $height < 1000)) {
            return $width >= 1600 ? 'SOURCE_RESAMPLE_AND_CROP_READY' : 'SOURCE_RESAMPLE_READY';
        }

        return $width >= 1600 && $height >= 1000 ? 'SOURCE_NATIVE_READY' : 'SOURCE_CROP_READY';
    }

    private function hasVariants(Media $media, string $contentType): bool
    {
        /** @var array<string, array<string, string>> $variants */
        $variants = (array) $media->responsive_variants;
        $profiles = (array) ($contentType === 'blog' ? config('media_excellence.blog_profiles') : config('media_excellence.recipe_profiles'));
        foreach (array_keys($profiles) as $profile) {
            $variant = $variants[$profile] ?? null;
            if (! is_array($variant) || ! isset($variant['jpeg']) || ! is_string($variant['jpeg']) || ! Storage::disk('public')->exists($variant['jpeg'])) {
                return false;
            }
        }

        return true;
    }
}
