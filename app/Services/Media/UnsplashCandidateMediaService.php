<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Models\AuditLog;
use App\Models\Media;
use App\Models\RecipeMediaCandidate;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Binds an approved candidate as an external hotlink and tracks once. */
final class UnsplashCandidateMediaService
{
    public function __construct(private readonly UnsplashRecipeImageProvider $provider) {}

    public function approve(RecipeMediaCandidate $candidate, User $reviewer, string $altText, string $reviewNotes): Media
    {
        abort_if($reviewer->isSystemReviewer(), 403, 'Sistem hesabı medya onayı veremez.');
        abort_if($candidate->selected_by_user_id !== null && (int) $candidate->selected_by_user_id === (int) $reviewer->id, 403, 'Aynı editör seçtiği adayı final olarak onaylayamaz.');
        if ($candidate->provider === 'unsplash' && $candidate->status === 'approved' && $candidate->media_id !== null) {
            $existing = Media::query()->find($candidate->media_id);
            if ($existing instanceof Media) {
                return $existing;
            }
        }
        if ($candidate->provider !== 'unsplash' || $candidate->status !== 'selected_for_review' || $candidate->recipe === null || ! $this->safeImage($candidate->source_url)) {
            throw ValidationException::withMessages(['candidate' => 'Bu Unsplash adayı final onay için uygun değil.']);
        }

        return DB::transaction(function () use ($candidate, $reviewer, $altText, $reviewNotes): Media {
            if ($candidate->download_tracked_at === null && $candidate->tracking_status !== 'failed') {
                $tracking = $this->provider->trackDownload((string) $candidate->download_location);
                $candidate->update(['tracking_status' => $tracking['status'], 'tracking_error_code' => $tracking['error_code'], 'download_tracked_at' => $tracking['status'] === 'tracked' ? now() : null]);
                if ($tracking['status'] !== 'tracked') {
                    throw ValidationException::withMessages(['candidate' => 'Unsplash tracking tamamlanamadı; otomatik tekrar denenmeyecek. Yönetici daha sonra güvenli olarak yeniden değerlendirebilir.']);
                }
            }
            $duplicate = Media::query()->where('source_name', 'Unsplash')->where('source_url', $candidate->source_url)->where('mediable_id', '!=', $candidate->recipe_id)->exists();
            if ($duplicate) {
                throw ValidationException::withMessages(['candidate' => 'Bu Unsplash fotoğrafı başka bir tarifte zaten onaylı.']);
            }
            $media = $candidate->recipe->media()->create(['kind' => 'external_hotlinked_media', 'disk' => 'external', 'path' => null, 'mime_type' => 'image/jpeg', 'size' => 0, 'sort_order' => 0, 'visibility' => 'public', 'media_status' => 'approved_real_media', 'fidelity_status' => 'strong_match', 'fidelity_confidence' => 100, 'license_review_status' => 'approved', 'attribution_review_status' => 'approved', 'optimization_status' => 'provider_managed', 'alt_text' => $altText, 'width' => $candidate->width, 'height' => $candidate->height, 'source_name' => 'Unsplash', 'source_url' => $candidate->source_url, 'license' => 'Unsplash License', 'attribution' => 'Fotoğraf: '.$candidate->photographer.' / Unsplash', 'verification_status' => 'verified', 'confidence' => 100, 'reviewed_at' => now(), 'media_reviewer_user_id' => $reviewer->id, 'media_reviewed_at' => now(), 'license_reviewed_at' => now(), 'attribution_reviewed_at' => now(), 'health_scanned_at' => now(), 'health_rule_version' => config('media_excellence.health_rule_version'), 'approved_by_user_id' => $reviewer->id, 'approved_at' => now(), 'provider_metadata' => ['provider' => 'unsplash', 'hotlink_only' => true, 'photographer' => $candidate->photographer, 'photographer_url' => $candidate->photographer_url, 'unsplash_page_url' => $candidate->external_page_url, 'small_url' => $candidate->small_url, 'thumb_url' => $candidate->thumb_url, 'download_tracking_status' => 'tracked']]);
            $candidate->update(['status' => 'approved', 'media_id' => $media->id]);
            AuditLog::query()->create(['user_id' => $reviewer->id, 'action' => 'unsplash_editorial_candidate.approved', 'auditable_type' => $candidate->recipe->getMorphClass(), 'auditable_id' => $candidate->recipe_id, 'metadata' => ['recipe_public_id' => $candidate->recipe->public_id, 'candidate_public_id' => $candidate->public_id, 'media_public_id' => $media->public_id, 'review_notes' => $reviewNotes, 'download_tracking' => 'tracked']]);

            return $media;
        });
    }

    private function safeImage(mixed $url): bool
    {
        $parts = is_string($url) ? parse_url($url) : false;

        return is_array($parts) && ($parts['scheme'] ?? null) === 'https' && strtolower((string) ($parts['host'] ?? '')) === 'images.unsplash.com' && ! isset($parts['user'], $parts['pass']) && (! isset($parts['port']) || (int) $parts['port'] === 443);
    }
}
