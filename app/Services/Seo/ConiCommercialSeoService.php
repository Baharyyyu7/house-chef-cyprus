<?php

declare(strict_types=1);

namespace App\Services\Seo;

/**
 * ConiSEOBot: Ticari Değer Odaklı SEO ve Arama Stratejisti Motoru.
 *
 * Arama hacmini (volume) sıralamanın kendisi olarak değil, altı girdiden biri olarak
 * değerlendiren ve her tahmini denetlenebilir matematiksel formüllerle (LOW, MID, HIGH)
 * sunan bağımsız servis.
 *
 * Formül:
 * Beklenen Değer = Arama Hacmi × Hedef CTR × SERP İndirimi × Ürün Uyumu × Dönüşüm Oranı × Dönüşüm Başı Değer
 */
final class ConiCommercialSeoService
{
    public const INTENT_TRANSACTIONAL = 'TRANSACTIONAL';
    public const INTENT_COMMERCIAL = 'COMMERCIAL';
    public const INTENT_INFORMATIONAL = 'INFORMATIONAL';
    public const INTENT_NAVIGATIONAL = 'NAVIGATIONAL';

    public const FIT_DIRECT = 1.0;
    public const FIT_ADJACENT = 0.6;
    public const FIT_TANGENTIAL = 0.25;
    public const FIT_NONE = 0.0;

    /**
     * CTR Benchmark Değerleri (SERP pozisyonlarına göre)
     */
    public const CTR_BENCHMARKS = [
        'pos_1' => ['low' => 0.20, 'mid' => 0.28, 'high' => 0.35],
        'pos_2' => ['low' => 0.12, 'mid' => 0.15, 'high' => 0.18],
        'pos_3' => ['low' => 0.08, 'mid' => 0.11, 'high' => 0.14],
        'pos_4_10' => ['low' => 0.02, 'mid' => 0.04, 'high' => 0.06],
    ];

    /**
     * SERP Özellik İndirim Çarpanları
     */
    public const SERP_DISCOUNTS = [
        'clean_organic' => 1.0,
        'local_pack' => 0.70,
        'recipe_cards' => 0.65,
        'ai_overview' => 0.55,
        'ads_and_ai' => 0.40,
    ];

    /**
     * Dönüşüm Başı Ortalama Net Değer (TRY cinsinden)
     */
    public const CONVERSION_VALUES = [
        'lead_private_chef' => ['low' => 3500.0, 'mid' => 5500.0, 'high' => 8500.0], // Özel şef / villa catering rezervasyon kârı
        'cook_mode_engagement' => ['low' => 30.0, 'mid' => 60.0, 'high' => 120.0], // Dijital mutfak araçları kullanıcı değeri
        'recipe_ad_impression' => ['low' => 0.03, 'mid' => 0.06, 'high' => 0.12], // AdSense / programatik reklam değeri
    ];

    /**
     * Tekil bir anahtar kelimeyi ConiSEOBot kurallarıyla değerlendirir.
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public function evaluateKeyword(string $keyword, array $params = []): array
    {
        $normalized = mb_strtolower(trim($keyword), 'UTF-8');
        $intent = $params['intent'] ?? $this->detectIntent($normalized);
        $productFit = (float) ($params['product_fit'] ?? $this->detectProductFit($normalized, $intent));
        $volume = (int) ($params['volume'] ?? $this->estimateVolume($normalized));
        $serpFeature = $params['serp_feature'] ?? $this->detectSerpFeature($normalized);
        $serpDiscount = (float) (self::SERP_DISCOUNTS[$serpFeature] ?? 1.0);
        $conversionType = $params['conversion_type'] ?? $this->detectConversionType($intent, $productFit);
        $locale = $params['locale'] ?? $this->detectLocale($normalized);

        $ctrKey = $params['target_rank_tier'] ?? 'pos_3'; // Varsayılan hedef ilk 3 sıra
        $ctr = self::CTR_BENCHMARKS[$ctrKey] ?? self::CTR_BENCHMARKS['pos_3'];

        $cr = $this->getConversionRateRange($intent, $productFit);
        $vpc = self::CONVERSION_VALUES[$conversionType] ?? self::CONVERSION_VALUES['cook_mode_engagement'];

        // Matematiksel Hesaplama: LOW, MID, HIGH
        $expectedClicks = [
            'low' => round($volume * $ctr['low'] * $serpDiscount, 1),
            'mid' => round($volume * $ctr['mid'] * $serpDiscount, 1),
            'high' => round($volume * $ctr['high'] * $serpDiscount, 1),
        ];

        $expectedConversions = [
            'low' => round($expectedClicks['low'] * $cr['low'], 2),
            'mid' => round($expectedClicks['mid'] * $cr['mid'], 2),
            'high' => round($expectedClicks['high'] * $cr['high'], 2),
        ];

        $expectedValueTRY = [
            'low' => round($expectedConversions['low'] * $vpc['low'], 2),
            'mid' => round($expectedConversions['mid'] * $vpc['mid'], 2),
            'high' => round($expectedConversions['high'] * $vpc['high'], 2),
        ];

        // EUR karşılığı (1 EUR ~ 38 TRY baz alınarak)
        $eurRate = 38.0;
        $expectedValueEUR = [
            'low' => round($expectedValueTRY['low'] / $eurRate, 2),
            'mid' => round($expectedValueTRY['mid'] / $eurRate, 2),
            'high' => round($expectedValueTRY['high'] / $eurRate, 2),
        ];

        $priorityScore = $this->calculatePriorityScore($expectedValueTRY['mid'], $productFit, $volume);

        return [
            'keyword' => $keyword,
            'normalized_key' => $normalized,
            'locale' => $locale,
            'intent' => $intent,
            'product_fit' => [
                'score' => $productFit,
                'label' => $this->getProductFitLabel($productFit),
            ],
            'volume' => $volume,
            'serp' => [
                'feature' => $serpFeature,
                'discount' => $serpDiscount,
            ],
            'conversion_type' => $conversionType,
            'benchmarks' => [
                'ctr' => $ctr,
                'conversion_rate' => $cr,
                'value_per_conversion_try' => $vpc,
            ],
            'expected_clicks_monthly' => $expectedClicks,
            'expected_conversions_monthly' => $expectedConversions,
            'expected_value_monthly_try' => $expectedValueTRY,
            'expected_value_monthly_eur' => $expectedValueEUR,
            'priority_score' => $priorityScore,
            'recommendation' => $this->generateActionableAdvice($intent, $productFit, $expectedValueTRY['mid']),
            'evaluated_at' => now()->toIso8601String(),
        ];
    }

    /**
     * House Chef Cyprus için hazır ticari anahtar kelime portföyünü döner.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getPredefinedCyprusKeywordPortfolio(): array
    {
        $keywords = [
            // Tier 1: High Ticket Private Chef & Catering
            ['keyword' => 'kıbrıs özel şef', 'volume' => 320, 'intent' => self::INTENT_TRANSACTIONAL, 'product_fit' => self::FIT_DIRECT, 'locale' => 'tr', 'serp_feature' => 'local_pack', 'conversion_type' => 'lead_private_chef'],
            ['keyword' => 'cyprus private chef', 'volume' => 750, 'intent' => self::INTENT_TRANSACTIONAL, 'product_fit' => self::FIT_DIRECT, 'locale' => 'en', 'serp_feature' => 'clean_organic', 'conversion_type' => 'lead_private_chef'],
            ['keyword' => 'girne villa aşçısı', 'volume' => 190, 'intent' => self::INTENT_TRANSACTIONAL, 'product_fit' => self::FIT_DIRECT, 'locale' => 'tr', 'serp_feature' => 'local_pack', 'conversion_type' => 'lead_private_chef'],
            ['keyword' => 'private chef kyrenia villa', 'volume' => 450, 'intent' => self::INTENT_TRANSACTIONAL, 'product_fit' => self::FIT_DIRECT, 'locale' => 'en', 'serp_feature' => 'clean_organic', 'conversion_type' => 'lead_private_chef'],
            ['keyword' => 'kıbrıs evde yemek catering', 'volume' => 280, 'intent' => self::INTENT_TRANSACTIONAL, 'product_fit' => self::FIT_DIRECT, 'locale' => 'tr', 'serp_feature' => 'local_pack', 'conversion_type' => 'lead_private_chef'],
            ['keyword' => 'частный повар кипр северный', 'volume' => 380, 'intent' => self::INTENT_TRANSACTIONAL, 'product_fit' => self::FIT_DIRECT, 'locale' => 'ru', 'serp_feature' => 'clean_organic', 'conversion_type' => 'lead_private_chef'],

            // Tier 2: Commercial & Comparative
            ['keyword' => 'kıbrıs özel şef fiyatları', 'volume' => 410, 'intent' => self::INTENT_COMMERCIAL, 'product_fit' => self::FIT_DIRECT, 'locale' => 'tr', 'serp_feature' => 'ai_overview', 'conversion_type' => 'lead_private_chef'],
            ['keyword' => 'hire chef for villa cyprus', 'volume' => 520, 'intent' => self::INTENT_COMMERCIAL, 'product_fit' => self::FIT_DIRECT, 'locale' => 'en', 'serp_feature' => 'clean_organic', 'conversion_type' => 'lead_private_chef'],
            ['keyword' => 'kıbrıs meze siparişi', 'volume' => 340, 'intent' => self::INTENT_COMMERCIAL, 'product_fit' => self::FIT_ADJACENT, 'locale' => 'tr', 'serp_feature' => 'local_pack', 'conversion_type' => 'lead_private_chef'],

            // Tier 3: Informational with High Traffic & Conversion Funnels
            ['keyword' => 'kıbrıs usulü kolokas tarifi', 'volume' => 2400, 'intent' => self::INTENT_INFORMATIONAL, 'product_fit' => self::FIT_TANGENTIAL, 'locale' => 'tr', 'serp_feature' => 'recipe_cards', 'conversion_type' => 'cook_mode_engagement'],
            ['keyword' => 'hellim böreği nasıl yapılır', 'volume' => 1800, 'intent' => self::INTENT_INFORMATIONAL, 'product_fit' => self::FIT_TANGENTIAL, 'locale' => 'tr', 'serp_feature' => 'recipe_cards', 'conversion_type' => 'cook_mode_engagement'],
            ['keyword' => 'kleftiko tarifi fırında', 'volume' => 1500, 'intent' => self::INTENT_INFORMATIONAL, 'product_fit' => self::FIT_TANGENTIAL, 'locale' => 'tr', 'serp_feature' => 'recipe_cards', 'conversion_type' => 'cook_mode_engagement'],
            ['keyword' => 'traditional cyprus food recipes', 'volume' => 2900, 'intent' => self::INTENT_INFORMATIONAL, 'product_fit' => self::FIT_TANGENTIAL, 'locale' => 'en', 'serp_feature' => 'ai_overview', 'conversion_type' => 'cook_mode_engagement'],
        ];

        return array_map(fn (array $kw) => $this->evaluateKeyword($kw['keyword'], $kw), $keywords);
    }

    /**
     * Portföy özet metriklerini hesaplar.
     *
     * @return array<string, mixed>
     */
    public function getPortfolioSummary(): array
    {
        $portfolio = $this->getPredefinedCyprusKeywordPortfolio();

        $totalVolume = array_sum(array_column($portfolio, 'volume'));
        $totalMonthlyMidTRY = 0.0;
        $totalMonthlyMidEUR = 0.0;
        $intentCounts = [];
        $localeCounts = [];

        foreach ($portfolio as $item) {
            $totalMonthlyMidTRY += $item['expected_value_monthly_try']['mid'];
            $totalMonthlyMidEUR += $item['expected_value_monthly_eur']['mid'];

            $intent = (string) $item['intent'];
            $intentCounts[$intent] = ($intentCounts[$intent] ?? 0) + 1;

            $locale = (string) $item['locale'];
            $localeCounts[$locale] = ($localeCounts[$locale] ?? 0) + 1;
        }

        // Öncelik sırasına göre sırala
        usort($portfolio, fn ($a, $b) => $b['expected_value_monthly_try']['mid'] <=> $a['expected_value_monthly_try']['mid']);

        return [
            'total_keywords' => count($portfolio),
            'total_volume' => $totalVolume,
            'potential_monthly_try' => round($totalMonthlyMidTRY, 2),
            'potential_monthly_eur' => round($totalMonthlyMidEUR, 2),
            'potential_yearly_try' => round($totalMonthlyMidTRY * 12, 2),
            'intent_distribution' => $intentCounts,
            'locale_distribution' => $localeCounts,
            'top_opportunities' => array_slice($portfolio, 0, 5),
            'all_items' => $portfolio,
        ];
    }

    private function detectIntent(string $keyword): string
    {
        if (preg_match('/(fiyat|kirala|şef|aşçı|sipariş|hire|chef|booking|catering|satın|price|cost)/u', $keyword)) {
            return self::INTENT_TRANSACTIONAL;
        }
        if (preg_match('/(en iyi|farkı|karşılaştır|yorum|öneri|best|vs|review|menu)/u', $keyword)) {
            return self::INTENT_COMMERCIAL;
        }
        if (preg_match('/(house\s*chef|housechefcyprus)/u', $keyword)) {
            return self::INTENT_NAVIGATIONAL;
        }

        return self::INTENT_INFORMATIONAL;
    }

    private function detectProductFit(string $keyword, string $intent): float
    {
        if (preg_match('/(özel şef|private chef|villa aşçısı|villa chef|catering|chef hire)/u', $keyword)) {
            return self::FIT_DIRECT;
        }
        if (preg_match('/(villa|tatil|yemek sipariş|meze|davet|etkinlik|akşam yemeği)/u', $keyword)) {
            return self::FIT_ADJACENT;
        }
        if (preg_match('/(tarif|nasıl yapılır|kolokas|hellim|şeftali kebabı|recipe)/u', $keyword)) {
            return self::FIT_TANGENTIAL;
        }

        return self::FIT_NONE;
    }

    private function detectSerpFeature(string $keyword): string
    {
        if (preg_match('/(tarif|recipe|nasıl yapılır)/u', $keyword)) {
            return 'recipe_cards';
        }
        if (preg_match('/(kıbrıs|girne|lefkoşa|iskele|kyrenia)/u', $keyword) && preg_match('/(şef|restoran|catering)/u', $keyword)) {
            return 'local_pack';
        }
        if (preg_match('/(nedir|ne kadar|fiyatları)/u', $keyword)) {
            return 'ai_overview';
        }

        return 'clean_organic';
    }

    private function detectConversionType(string $intent, float $productFit): string
    {
        if ($productFit >= self::FIT_ADJACENT && $intent === self::INTENT_TRANSACTIONAL) {
            return 'lead_private_chef';
        }
        if ($productFit >= self::FIT_TANGENTIAL) {
            return 'cook_mode_engagement';
        }

        return 'recipe_ad_impression';
    }

    private function detectLocale(string $keyword): string
    {
        if (preg_match('/[а-яА-ЯёЁ]/u', $keyword)) {
            return 'ru';
        }
        if (preg_match('/(private|chef|hire|cyprus|villa|recipe)/i', $keyword)) {
            return 'en';
        }
        if (preg_match('/(koch|rezepte|zypern)/i', $keyword)) {
            return 'de';
        }

        return 'tr';
    }

    /**
     * @return array{low: float, mid: float, high: float}
     */
    private function getConversionRateRange(string $intent, float $productFit): array
    {
        if ($productFit >= self::FIT_DIRECT && $intent === self::INTENT_TRANSACTIONAL) {
            return ['low' => 0.02, 'mid' => 0.038, 'high' => 0.065];
        }
        if ($productFit >= self::FIT_ADJACENT) {
            return ['low' => 0.01, 'mid' => 0.022, 'high' => 0.040];
        }
        if ($productFit >= self::FIT_TANGENTIAL) {
            return ['low' => 0.003, 'mid' => 0.008, 'high' => 0.018];
        }

        return ['low' => 0.001, 'mid' => 0.003, 'high' => 0.008];
    }

    private function estimateVolume(string $keyword): int
    {
        $len = mb_strlen($keyword);
        if (preg_match('/(kıbrıs|cyprus)/u', $keyword)) {
            return max(150, 600 - ($len * 10));
        }

        return max(50, 400 - ($len * 8));
    }

    private function calculatePriorityScore(float $monthlyValTRY, float $fit, int $volume): int
    {
        $valScore = min(50, ($monthlyValTRY / 1000) * 5);
        $fitScore = $fit * 30;
        $volScore = min(20, ($volume / 100));

        return (int) round($valScore + $fitScore + $volScore);
    }

    private function getProductFitLabel(float $fit): string
    {
        return match ($fit) {
            self::FIT_DIRECT => 'Doğrudan Ürün/Hizmet (1.0)',
            self::FIT_ADJACENT => 'Yakın İlgili İhtiyaç (0.6)',
            self::FIT_TANGENTIAL => 'Teğetsel / İçerik (0.25)',
            default => 'Sıfır Değer (0.0)',
        };
    }

    private function generateActionableAdvice(string $intent, float $productFit, float $monthlyVal): string
    {
        if ($productFit >= self::FIT_DIRECT) {
            return 'Öncelikli Gelir Sayfası: Above-the-fold Özel Şef rezervasyon formu ve doğrudan WhatsApp CTA butonu yerleştirilmeli.';
        }
        if ($productFit >= self::FIT_ADJACENT) {
            return 'Köprü Sayfa: Villa tatili ve catering rehberi hazırlanıp ana rezervasyon sayfasına net iç bağlantı (internal link) kurulmalı.';
        }
        if ($monthlyVal > 500) {
            return 'Hacim & AEO Sayfası: İlk cümle kuralına uygun net tarif verilmeli; ardından "Pişir Modu" ve "Dolabım" etkileşim butonları öne çıkarılmalı.';
        }

        return 'Bilgi Sayfası: Standart tarif şablonu ve AdSense birimleri korunmalı, aşırı operasyonel kaynak harcanmamalı.';
    }
}
