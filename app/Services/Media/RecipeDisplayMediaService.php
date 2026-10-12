<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Models\Media;
use App\Models\Recipe;

/**
 * 2 ve 3 numaralı çözümlerin birleşimi (Hibrit Akıllı Görsel Sistemi):
 * 1. Tarifin kendi onaylı fotoğrafı varsa (Kıbrıs Kleftiko, Şeftali Kebabı, Kolokas vb.) onu gösterir.
 * 2. Uygun fotoğraf yoksa yalnız markalı temsili görseli gösterir.
 *
 * Bir tarifin fotoğrafı yokken başka bir yemeğin fotoğrafını göstermek, görseli
 * tarif kimliği hakkında yanlış bir iddiaya dönüştürür. Bu servis nedenle
 * tarif adına, kategoriye veya mutfağa göre stok fotoğraf eşleştirmez.
 */
final class RecipeDisplayMediaService
{
    public function __construct(
        private readonly PublicMediaEligibilityService $eligibilityService
    ) {}

    /**
     * @return array{
     *     has_specific_media: bool,
     *     media: ?Media,
     *     card_url: string,
     *     hero_url: string,
     *     alt: string,
     *     is_synthetic: bool,
     *     is_fallback: bool,
     *     badge_label: ?string
     * }
     */
    public function resolve(Recipe $recipe): array
    {
        // Seviye 1: Tarifin kendi onaylı, doğrulanmış görseli varsa (Kıbrıs Kleftiko, Şeftali vb.)
        $specific = $this->eligibilityService->firstEligible($recipe->media);

        if ($specific !== null && $specific->publicUrl() !== null) {
            $cardUrl = $specific->responsiveUrl(800) ?? $specific->publicUrl();
            $heroUrl = $specific->responsiveUrl(1600) ?? $specific->publicUrl();

            return [
                'has_specific_media' => true,
                'media' => $specific,
                'card_url' => (string) $cardUrl,
                'hero_url' => (string) $heroUrl,
                // Kayıtlı alt Türkçe cümle ("... servis edilmiş hâlde"); yabancı
                // sayfada "AI generated image: <Türkçe cümle>" basılıyordu.
                'alt' => (string) (turkce_sayfa() ? ($specific->alt_text ?: $recipe->title) : ($recipe->translated('title') ?: $recipe->title)),
                'is_synthetic' => (bool) $specific->generated_by_ai,
                'is_fallback' => false,
                'badge_label' => null,
            ];
        }

        $fallback = $recipe->media->first(function (Media $media): bool {
            return $media->visibility === 'public'
                && ($media->is_placeholder || $media->verification_status === 'branded_fallback' || $media->media_status === 'branded_fallback')
                && $media->publicUrl() !== null;
        });
        $fallbackUrl = $fallback?->publicUrl() ?? url(config('brand.assets.og_default'));

        return [
            'has_specific_media' => false,
            'media' => null,
            'card_url' => $fallbackUrl,
            'hero_url' => $fallbackUrl,
            'alt' => __('card.photo_preparing_sub'),
            'is_synthetic' => false,
            'is_fallback' => true,
            'badge_label' => __('card.photo_preparing'),
        ];
    }
}
