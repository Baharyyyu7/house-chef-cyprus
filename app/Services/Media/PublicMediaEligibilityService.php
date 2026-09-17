<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Models\Media;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

/**
 * The final public-facing media gate.
 *
 * A database status is an editorial workflow signal, not proof that a file is
 * present, unique, responsive, or still attached to the dish/article it was
 * reviewed for. Public templates, structured data, and image sitemaps must use
 * this service instead of trusting an approval label alone.
 */
final class PublicMediaEligibilityService
{
    /** @param Collection<int, Media>|iterable<Media> $media */
    public function firstEligible(iterable $media): ?Media
    {
        foreach ($media as $item) {
            if ($item instanceof Media && $this->isEligible($item)) {
                return $item;
            }
        }

        return null;
    }

    public function isEligible(?Media $media): bool
    {
        return $this->reason($media) === null;
    }

    /**
     * hero_media_id sabit bir kayda işaret eder ve o kayıt her an yayına
     * uygunluğunu yitirebilir: dosyası kaybolabilir, denetimde düşürülebilir,
     * onayı geri alınabilir. Bir yazının dört medyası varken hero'nun düşmüş
     * olanı göstermesi yüzünden onaylı fotoğraf hiç görünmüyordu.
     *
     * İşaretçi geçerliyse ona saygı gösterilir; değilse yazının uygun olan ilk
     * görseline düşülür.
     *
     * @param  iterable<Media>  $media
     */
    public function heroOrFirstEligible(?Media $hero, iterable $media): ?Media
    {
        if ($this->isEligible($hero)) {
            return $hero;
        }

        return $this->firstEligible($media);
    }

    /**
     * Returns a stable, non-sensitive reason suitable for internal audits.
     */
    public function reason(?Media $media): ?string
    {
        if (! $media instanceof Media || ! $media->isApprovedRealMedia()) {
            return 'approval_incomplete';
        }

        if ($this->hasMismatchedRecipeIdentity($media)) {
            return 'recipe_identity_mismatch';
        }

        if (! in_array($media->fidelity_status, ['strong_match', 'approved'], true)
            || (int) $media->fidelity_confidence < (int) config('media_excellence.public_gate.minimum_fidelity_confidence', 90)) {
            return 'fidelity_not_confirmed';
        }

        if (! $this->hasHumanApproval($media)) {
            return 'human_approval_missing';
        }

        if (! $this->hasAccessibleSource($media)) {
            return 'source_unavailable';
        }

        if (! $this->hasRequiredVariants($media)) {
            return 'responsive_variants_incomplete';
        }

        if ($this->isDuplicated($media)) {
            return 'duplicate_media_usage';
        }

        return null;
    }

    private function hasMismatchedRecipeIdentity(Media $media): bool
    {
        if (! is_string($media->path) || $media->path === '') {
            return false;
        }

        if (! preg_match('#(?:^|/)media/approved-synthetic/recipe/([^/]+)/#', $media->path, $matches)) {
            return false;
        }

        $recipe = $media->relationLoaded('mediable') ? $media->mediable : $media->mediable()->first();

        return $recipe instanceof Recipe && (string) ($matches[1] ?? '') !== (string) $recipe->public_id;
    }

    private function hasHumanApproval(Media $media): bool
    {
        if ($media->media_reviewer_user_id === null || $media->approved_by_user_id === null
            || $media->media_reviewed_at === null || $media->approved_at === null) {
            return false;
        }

        $reviewer = $media->relationLoaded('reviewedBy') ? $media->reviewedBy : $media->reviewedBy()->first();
        $approver = $media->relationLoaded('approvedBy') ? $media->approvedBy : $media->approvedBy()->first();

        return $reviewer instanceof User
            && $approver instanceof User
            && ! $reviewer->isSystemReviewer()
            && ! $approver->isSystemReviewer();
    }

    private function hasAccessibleSource(Media $media): bool
    {
        if ($media->kind === 'external_hotlinked_media') {
            return $media->isApprovedUnsplash();
        }

        return $media->disk === 'public'
            && is_string($media->path)
            && $media->path !== ''
            && Storage::disk('public')->exists($media->path)
            && $media->publicUrl() !== null;
    }

    private function hasRequiredVariants(Media $media): bool
    {
        if ($media->isApprovedUnsplash()) {
            return true;
        }

        $variants = $this->variants($media);
        $required = ['thumbnail', 'square', 'card', 'open_graph', 'hero'];

        foreach ($required as $profile) {
            $variant = $variants[$profile] ?? null;
            if (! is_array($variant)
                || ! is_string($variant['jpeg'] ?? null)
                || ! is_string($variant['webp'] ?? null)
                || ! Storage::disk('public')->exists($variant['jpeg'])
                || ! Storage::disk('public')->exists($variant['webp'])) {
                return false;
            }
        }

        return true;
    }

    /** @return array<string, array<string, mixed>> */
    private function variants(Media $media): array
    {
        $raw = $media->getRawOriginal('responsive_variants');
        $items = is_string($raw) && $raw !== '' ? json_decode($raw, true) : [];
        if (! is_array($items)) {
            return [];
        }

        $indexed = [];
        foreach ($items as $item) {
            if (is_array($item) && is_string($item['name'] ?? null)) {
                $indexed[$item['name']] = $item;
            }
        }

        return $indexed;
    }

    /**
     * Kural, aynı dosyanın iki yerde birden yayına çıkmasını engellemek için
     * var. Karşılaştırma her kayda değil, yalnız gerçekten yayında olanlara
     * yapılır.
     *
     * Eskiden onay bekleyen ikizler de sayılıyordu ve bu bir kilitlenme
     * üretiyordu: içe aktarma iki kez çalıştığında aynı dosya için iki kayıt
     * oluşuyor, her biri diğerini kopya sayıyor ve ikisi de hiçbir zaman
     * onaylanamıyordu. Onay ekranındaki sekiz görselin sekizi de bu yüzden
     * "onaya hazır değil" veriyordu.
     *
     * Daralma kuralı zayıflatmıyor: ilk kopya onaylanıp yayına girdiği anda
     * ikincisi yine engelleniyor. Toplu onay da kayıtları tek tek kaydettiği
     * için aynı partide iki kopya birden geçemiyor.
     */
    private function isDuplicated(Media $media): bool
    {
        $query = Media::query()
            ->whereKeyNot($media->getKey())
            ->where('visibility', 'public')
            ->where('media_status', 'approved_real_media')
            ->whereNotNull('approved_by_user_id');

        if (is_string($media->checksum_sha256) && $media->checksum_sha256 !== '') {
            return $query->where('checksum_sha256', $media->checksum_sha256)->exists();
        }

        if (is_string($media->perceptual_hash) && $media->perceptual_hash !== '') {
            return $query->where('perceptual_hash', $media->perceptual_hash)->exists();
        }

        if ($media->kind === 'external_hotlinked_media' && is_string($media->source_url) && $media->source_url !== '') {
            return $query->where('source_url', $media->source_url)->exists();
        }

        return is_string($media->path) && $media->path !== ''
            && $query->where('disk', $media->disk)->where('path', $media->path)->exists();
    }
}
