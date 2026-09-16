<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Models\Media;
use App\Models\Recipe;

/**
 * 2 ve 3 numaralı çözümlerin birleşimi (Hibrit Akıllı Görsel Sistemi):
 * 1. Tarifin kendi onaylı fotoğrafı varsa (Kıbrıs Kleftiko, Şeftali Kebabı, Kolokas vb.) onu gösterir.
 * 2. Yoksa tarifin adına/türüne özel yüksek çözünürlüklü gastronomi fotoğrafını (Unsplash CC0) eşleştirir.
 * 3. Kalanlar için kategorinin (Çorba, Tatlı, Hamur İşi, Meze vb.) zengin imza görseline zarifçe düşer.
 * 
 * Sıfır token maliyeti, sıfır API kotası, sıfır disk şişmesi.
 * Sitede tek bir boş kutu veya "Fotoğraf Hazırlanıyor" uyarısı bırakmaz.
 */
final class RecipeDisplayMediaService
{
    /** @var array<string, string> Yemek adına/türüne göre özel fotoğraf eşleştirmeleri */
    private const DISH_SPECIFIC_PHOTOS = [
        // Çorbalar
        'mercimek' => 'https://images.unsplash.com/photo-1547592166-23ac45744acd',
        'ezogelin' => 'https://images.unsplash.com/photo-1547592166-23ac45744acd',
        'tarhana' => 'https://images.unsplash.com/photo-1547592180-85f173990554',
        'yayla' => 'https://images.unsplash.com/photo-1603105037880-880cd4edfb0d',
        'domates-corbasi' => 'https://images.unsplash.com/photo-1541832676-9b763b0239ab',
        'mantar-corbasi' => 'https://images.unsplash.com/photo-1547592166-23ac45744acd',
        
        // Et / Tavuk / Kebap
        'adana' => 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1',
        'iskender' => 'https://images.unsplash.com/photo-1529193591184-b1d58069ecdd',
        'kofte' => 'https://images.unsplash.com/photo-1529692236671-f1f6cf9683ba',
        'karniyarik' => 'https://images.unsplash.com/photo-1574484284002-952d92456975',
        'imam-bayildi' => 'https://images.unsplash.com/photo-1574484284002-952d92456975',
        'tavuk-sote' => 'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d',
        'tandir' => 'https://images.unsplash.com/photo-1544025162-d76694265947',
        'kavurma' => 'https://images.unsplash.com/photo-1544025162-d76694265947',
        'incik' => 'https://images.unsplash.com/photo-1544025162-d76694265947',

        // Kahvaltı / Yumurta
        'menemen' => 'https://images.unsplash.com/photo-1525351484163-7529414344d8',
        'sucuklu-yumurta' => 'https://images.unsplash.com/photo-1525351484163-7529414344d8',
        'omlet' => 'https://images.unsplash.com/photo-1525351484163-7529414344d8',
        'cilbir' => 'https://images.unsplash.com/photo-1525351484163-7529414344d8',

        // Mezeler / Salatalar
        'humus' => 'https://images.unsplash.com/photo-1577906096429-f73c2c312435',
        'babaganus' => 'https://images.unsplash.com/photo-1541544741938-0af808871cc0',
        'haydari' => 'https://images.unsplash.com/photo-1488477181946-6428a0291777',
        'cacik' => 'https://images.unsplash.com/photo-1488477181946-6428a0291777',
        'coban' => 'https://images.unsplash.com/photo-1540420773420-3366772f4999',
        'kisir' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd',
        'fava' => 'https://images.unsplash.com/photo-1541544741938-0af808871cc0',
        'semizotu' => 'https://images.unsplash.com/photo-1540420773420-3366772f4999',

        // Hamur İşleri
        'lahmacun' => 'https://images.unsplash.com/photo-1513104890138-7c749659a591',
        'pide' => 'https://images.unsplash.com/photo-1513104890138-7c749659a591',
        'borek' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff',
        'gozleme' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff',
        'pogaca' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff',
        'simit' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff',
        'manti' => 'https://images.unsplash.com/photo-1621996346565-e3d5d628169b',

        // Sebze / Zeytinyağlı
        'fasulye' => 'https://images.unsplash.com/photo-1540420773420-3366772f4999',
        'enginar' => 'https://images.unsplash.com/photo-1540420773420-3366772f4999',
        'bamya' => 'https://images.unsplash.com/photo-1540420773420-3366772f4999',
        'barbunya' => 'https://images.unsplash.com/photo-1540420773420-3366772f4999',
        'dolma' => 'https://images.unsplash.com/photo-1540420773420-3366772f4999',
        'sarma' => 'https://images.unsplash.com/photo-1540420773420-3366772f4999',
        'mucver' => 'https://images.unsplash.com/photo-1540420773420-3366772f4999',

        // Tatlılar
        'baklava' => 'https://images.unsplash.com/photo-1519676867240-f03562e64548',
        'sutlac' => 'https://images.unsplash.com/photo-1551024709-8f23befc6f87',
        'kunefe' => 'https://images.unsplash.com/photo-1519676867240-f03562e64548',
        'kazandibi' => 'https://images.unsplash.com/photo-1551024709-8f23befc6f87',
        'revani' => 'https://images.unsplash.com/photo-1519676867240-f03562e64548',
        'helva' => 'https://images.unsplash.com/photo-1519676867240-f03562e64548',
        'trilece' => 'https://images.unsplash.com/photo-1551024709-8f23befc6f87',

        // Deniz Ürünleri
        'levrek' => 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2',
        'cipura' => 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2',
        'balik' => 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2',
        'somon' => 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2',
        'karides' => 'https://images.unsplash.com/photo-1565680018434-b513d5e5fd47',

        // Pilav & Makarna
        'pilav' => 'https://images.unsplash.com/photo-1516684732162-798a0062be99',
        'makarna' => 'https://images.unsplash.com/photo-1621996346565-e3d5d628169b',

        // Kıbrıs Spesiyalleri (Varsayılan ada görselleri)
        'seftali' => 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1',
        'kleftiko' => 'https://images.unsplash.com/photo-1544025162-d76694265947',
        'kolokas' => 'https://images.unsplash.com/photo-1540420773420-3366772f4999',
        'molohiya' => 'https://images.unsplash.com/photo-1540420773420-3366772f4999',
        'hellim' => 'https://images.unsplash.com/photo-1533089860892-a7c6f0a88666',
        'flaouna' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff',
    ];

    /** @var array<string, string> Kategori bazlı zengin imza fotoğrafları */
    private const CATEGORY_FALLBACKS = [
        'corba' => 'https://images.unsplash.com/photo-1547592166-23ac45744acd', // Dumanı tüten kase çorba
        'ana-yemek' => 'https://images.unsplash.com/photo-1544025162-d76694265947', // Fırın et tabağı
        'zeytinyagli-sebze' => 'https://images.unsplash.com/photo-1540420773420-3366772f4999', // Taze zeytinyağlılar
        'salata-meze' => 'https://images.unsplash.com/photo-1541544741938-0af808871cc0', // Akdeniz meze tabağı
        'hamur-isi' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff', // Çıtır fırın böreği
        'tatli' => 'https://images.unsplash.com/photo-1519676867240-f03562e64548', // Altın rengi şerbetli tatlı
        'kahvalti' => 'https://images.unsplash.com/photo-1533089860892-a7c6f0a88666', // Zengin serpme kahvaltı
        'atistirmalik-pratik' => 'https://images.unsplash.com/photo-1565299585323-38d6b0865b47', // Pratik Akdeniz atıştırmalıkları
        'kibris-mutfagi' => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5', // Kıbrıs meyhane sofrası
    ];

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
                'alt' => (string) ($specific->alt_text ?: $recipe->title),
                'is_synthetic' => (bool) $specific->generated_by_ai,
                'is_fallback' => false,
                'badge_label' => null,
            ];
        }

        // Seviye 2: Yemek adına göre özel fotoğraf eşleştirmesi (Unsplash Açık Mutfak Kütüphanesi)
        $slug = $recipe->slug;
        $title = mb_strtolower($recipe->title);
        $matchedUrl = null;

        foreach (self::DISH_SPECIFIC_PHOTOS as $keyword => $url) {
            if (str_contains($slug, $keyword) || str_contains($title, $keyword)) {
                $matchedUrl = $url;
                break;
            }
        }

        // Seviye 3: Kategori veya Mutfak Mirası (Hiç eşleşmeyen kalırsa)
        if ($matchedUrl === null) {
            $catSlug = $recipe->category?->slug ?? 'ana-yemek';
            $cuisineSlug = $recipe->cuisine?->slug ?? '';

            $matchedUrl = ($cuisineSlug === 'kibris-mutfagi')
                ? (self::CATEGORY_FALLBACKS['kibris-mutfagi'] ?? self::CATEGORY_FALLBACKS['ana-yemek'])
                : (self::CATEGORY_FALLBACKS[$catSlug] ?? self::CATEGORY_FALLBACKS['ana-yemek']);
        }

        $cardUrl = $matchedUrl . '?auto=format&fit=crop&w=800&h=600&q=80';
        $heroUrl = $matchedUrl . '?auto=format&fit=crop&w=1600&h=1000&q=85';
        $catName = $recipe->category?->name ?? 'Geleneksel Mutfak';

        return [
            'has_specific_media' => false,
            'media' => null,
            'card_url' => $cardUrl,
            'hero_url' => $heroUrl,
            'alt' => $recipe->title . ' — ' . $catName,
            'is_synthetic' => false,
            'is_fallback' => true,
            'badge_label' => $catName,
        ];
    }
}
