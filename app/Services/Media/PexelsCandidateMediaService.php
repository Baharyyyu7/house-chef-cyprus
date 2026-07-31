<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Jobs\GenerateMediaThumbnailsJob;
use App\Models\AuditLog;
use App\Models\Media;
use App\Models\Recipe;
use App\Models\RecipeMediaCandidate;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Imports a Pexels search result only after an administrator selects it.
 * Remote URLs are never rendered publicly and candidates always begin private.
 */
final class PexelsCandidateMediaService
{
    public function __construct(
        private readonly PexelsRecipeImageProvider $provider,
        private readonly LocalMediaStorage $storage,
    ) {}

    public function import(Recipe $recipe, string $externalPhotoId): Media
    {
        $candidate = collect($this->provider->candidates($recipe))
            ->firstWhere('external_photo_id', $externalPhotoId);

        if (! is_array($candidate)) {
            throw ValidationException::withMessages(['candidate' => 'Seçilen Pexels adayı bulunamadı veya güvenlik politikasına uygun değil.']);
        }

        return $this->importCandidate($recipe, $candidate);
    }

    /**
     * Downloads the exact metadata record selected during editorial review.
     * It deliberately does not issue a second search, so the reviewed photo
     * cannot be substituted between selection and private import.
     */
    public function importStoredCandidate(RecipeMediaCandidate $candidate): Media
    {
        $recipe = $candidate->recipe;
        if (! $recipe instanceof Recipe || $candidate->provider !== 'pexels' || $candidate->source_url === null) {
            throw ValidationException::withMessages(['candidate' => 'Kaydedilmiş Pexels adayı private import için geçerli değil.']);
        }

        return $this->importCandidate($recipe, [
            'external_photo_id' => $candidate->external_photo_id,
            'source_url' => $candidate->source_url,
            'alt_text' => $candidate->alt_text ?? '',
            'photographer' => $candidate->photographer ?? 'Pexels',
            'photographer_url' => $candidate->photographer_url,
            'pexels_page_url' => $candidate->pexels_page_url,
            'attribution_text' => $candidate->photographer !== null ? 'Fotoğraf: '.$candidate->photographer.' · Pexels' : 'Fotoğraf: Pexels',
            'score' => $candidate->review_score,
        ]);
    }

    /** @param array{external_photo_id:string,source_url:string,alt_text:string,photographer:string,photographer_url:?string,pexels_page_url:?string,attribution_text:string,score:int} $candidate */
    private function importCandidate(Recipe $recipe, array $candidate): Media
    {
        $externalPhotoId = (string) $candidate['external_photo_id'];
        $alreadyUsed = Media::query()
            ->where('source_name', 'Pexels')
            ->where('provider_metadata->external_photo_id', $externalPhotoId)
            ->exists();
        if ($alreadyUsed) {
            throw ValidationException::withMessages(['candidate' => 'Bu Pexels görseli başka bir tarifte zaten kullanılıyor.']);
        }

        $sourceUrl = (string) $candidate['source_url'];
        $host = parse_url($sourceUrl, PHP_URL_HOST);
        if (! is_string($host) || ! Str::endsWith(Str::lower($host), '.pexels.com') || ! Str::startsWith($sourceUrl, 'https://')) {
            throw ValidationException::withMessages(['candidate' => 'Görsel kaynağı izin verilen Pexels hostuna ait değil.']);
        }

        try {
            $response = Http::accept('image/avif,image/webp,image/jpeg,image/png,image/*')
                ->timeout(15)
                ->get($sourceUrl);
        } catch (\Throwable) {
            throw ValidationException::withMessages(['candidate' => 'Pexels görseli güvenli süre içinde indirilemedi.']);
        }

        $bytes = $response->body();
        $maxBytes = max(1, (int) config('security.max_upload_bytes'));
        if (! $response->successful() || $bytes === '' || strlen($bytes) > $maxBytes || ! Str::startsWith((string) $response->header('Content-Type'), 'image/')) {
            throw ValidationException::withMessages(['candidate' => 'Pexels görseli dosya türü veya boyut politikasına uymuyor.']);
        }

        $temp = tempnam(sys_get_temp_dir(), 'house-chef-pexels-');
        if ($temp === false) {
            throw ValidationException::withMessages(['candidate' => 'Private medya alanı hazırlanamadı.']);
        }

        try {
            file_put_contents($temp, $bytes);
            $mime = (string) $response->header('Content-Type');
            $extension = match ($mime) {
                'image/png' => 'png',
                'image/webp' => 'webp',
                default => 'jpg',
            };
            $upload = new UploadedFile($temp, 'pexels-'.$externalPhotoId.'.'.$extension, $mime, null, true);
            $stored = $this->storage->storeImage($upload, 'media/pexels-review', 'private');
        } finally {
            @unlink($temp);
        }

        $checksum = hash_file('sha256', Storage::disk((string) $stored['disk'])->path((string) $stored['path']));
        if (Media::query()->where('checksum_sha256', $checksum)->exists()) {
            Storage::disk((string) $stored['disk'])->delete((string) $stored['path']);
            throw ValidationException::withMessages(['candidate' => 'Aynı görsel daha önce içe aktarılmış.']);
        }

        return $recipe->media()->create([
            'kind' => 'image',
            'disk' => $stored['disk'],
            'path' => $stored['path'],
            'mime_type' => $stored['mime_type'],
            'size' => $stored['size'],
            'sort_order' => 99,
            'visibility' => 'private',
            'alt_text' => $this->safeAltText($recipe, (string) $candidate['alt_text']),
            'width' => $stored['width'],
            'height' => $stored['height'],
            'processing_status' => 'ready_for_review',
            'thumbnail_status' => 'not_requested',
            'source_name' => 'Pexels',
            'source_url' => $sourceUrl,
            'license' => 'Pexels License',
            'attribution' => (string) $candidate['attribution_text'],
            'verification_status' => 'pending_review',
            'confidence' => 0,
            'is_placeholder' => false,
            'generated_by_ai' => false,
            'ai_review_status' => 'pending_review',
            'checksum_sha256' => $checksum,
            'perceptual_hash' => $this->perceptualHash((string) Storage::disk((string) $stored['disk'])->get((string) $stored['path'])),
            'provider_metadata' => [
                'provider' => 'pexels',
                'external_photo_id' => $externalPhotoId,
                'photographer' => (string) $candidate['photographer'],
                'photographer_url' => $candidate['photographer_url'],
                'pexels_page_url' => $candidate['pexels_page_url'],
                'source_status' => 'private_candidate',
                'license_status' => 'requires_admin_review',
                'candidate_score' => (int) $candidate['score'],
                'downloaded_at' => now()->toIso8601String(),
            ],
        ]);
    }

    public function approve(Media $media, User $reviewer, string $altText, ?string $notes = null): void
    {
        abort_unless($media->source_name === 'Pexels' && $media->visibility === 'private' && $media->verification_status === 'pending_review', 422, 'Bu Pexels adayı onay için uygun değil.');
        abort_unless(is_string($media->path) && Storage::disk($media->disk)->exists($media->path), 422, 'Private Pexels dosyası bulunamadı.');
        $recipe = $media->mediable;
        abort_unless($recipe instanceof Recipe, 422, 'Pexels adayı geçerli bir tarifle ilişkili değil.');

        $extension = pathinfo($media->path, PATHINFO_EXTENSION) ?: 'webp';
        $publicPath = 'media/pexels-approved/'.$recipe->public_id.'/'.$media->public_id.'.'.$extension;
        Storage::disk('public')->put($publicPath, Storage::disk($media->disk)->get($media->path));
        $recipe->media()->where('sort_order', 0)->whereKeyNot($media->id)->update(['sort_order' => 99]);
        $media->update([
            'disk' => 'public', 'path' => $publicPath, 'visibility' => 'public', 'sort_order' => 0,
            'alt_text' => $altText, 'processing_status' => 'pending', 'thumbnail_status' => 'pending',
            'verification_status' => 'verified', 'confidence' => 90, 'reviewed_at' => now(),
            'approved_by_user_id' => $reviewer->id, 'approved_at' => now(),
            'provider_metadata' => array_merge((array) $media->provider_metadata, ['source_status' => 'approved_public', 'review_notes' => $notes]),
        ]);
        GenerateMediaThumbnailsJob::dispatch($media->public_id);
        RecipeMediaCandidate::query()->where('media_id', $media->id)->update(['status' => 'approved']);
        AuditLog::query()->create(['user_id' => $reviewer->id, 'action' => 'pexels_candidate.approve', 'auditable_type' => $media->getMorphClass(), 'auditable_id' => $media->id, 'metadata' => ['media_public_id' => $media->public_id, 'recipe_public_id' => $recipe->public_id]]);
    }

    public function reject(Media $media, User $reviewer, ?string $notes = null): void
    {
        abort_unless($media->source_name === 'Pexels' && $media->visibility === 'private' && $media->verification_status === 'pending_review', 422, 'Bu Pexels adayı reddedilemez.');
        $media->update([
            'verification_status' => 'rejected', 'ai_review_status' => 'rejected', 'reviewed_at' => now(),
            'provider_metadata' => array_merge((array) $media->provider_metadata, ['source_status' => 'rejected', 'review_notes' => $notes]),
        ]);
        RecipeMediaCandidate::query()->where('media_id', $media->id)->update(['status' => 'rejected']);
        AuditLog::query()->create(['user_id' => $reviewer->id, 'action' => 'pexels_candidate.reject', 'auditable_type' => $media->getMorphClass(), 'auditable_id' => $media->id, 'metadata' => ['media_public_id' => $media->public_id]]);
    }

    private function safeAltText(Recipe $recipe, string $candidateAlt): string
    {
        $candidateAlt = trim(strip_tags($candidateAlt));

        return Str::limit($candidateAlt !== '' ? $recipe->title.' — '.$candidateAlt : $recipe->title.' için Pexels aday görseli', 255, '');
    }

    private function perceptualHash(string $bytes): string
    {
        $source = @imagecreatefromstring($bytes);
        if ($source === false) {
            return '';
        }
        $thumb = imagecreatetruecolor(8, 8);
        imagecopyresampled($thumb, $source, 0, 0, 0, 0, 8, 8, imagesx($source), imagesy($source));
        $values = [];
        for ($y = 0; $y < 8; $y++) {
            for ($x = 0; $x < 8; $x++) {
                $rgb = imagecolorat($thumb, $x, $y);
                $values[] = (($rgb >> 16) & 0xFF) + (($rgb >> 8) & 0xFF) + ($rgb & 0xFF);
            }
        }
        imagedestroy($source);
        imagedestroy($thumb);
        $average = array_sum($values) / count($values);

        return implode('', array_map(static fn (int $value): string => $value >= $average ? '1' : '0', $values));
    }
}
