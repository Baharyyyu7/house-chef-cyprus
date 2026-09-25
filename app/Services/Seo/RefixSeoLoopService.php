<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\BlogPost;
use App\Models\ContentOpportunity;
use App\Models\IntegrityReport;
use App\Models\Recipe;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

/**
 * RefixSeoBot: Autonomous Search Console Loop Engine.
 *
 * Implements Neil Agarwal's (@regalstreak / Refix) SEO Loop architecture:
 * Read (GSC Daily) -> Judge (6 Strategic Flags) -> Fix (Existing Pages) -> Write (Untargeted Drafts) -> Measure & Repeat.
 */
final class RefixSeoLoopService
{
    public const FLAG_ALMOST_THERE = 'almost_there';
    public const FLAG_NO_CLICKS = 'no_clicks';
    public const FLAG_DECAYING = 'decaying';
    public const FLAG_UNTARGETED = 'untargeted';
    public const FLAG_WRONG_INTENT = 'wrong_intent';
    public const FLAG_AI_MODE = 'ai_mode';
    public const FLAG_IGNORE = 'ignore';

    /**
     * Stratejik bayrak çarpanları (Önceliklendirme ağırlığı).
     */
    public const FLAG_WEIGHTS = [
        self::FLAG_ALMOST_THERE => 1.5,
        self::FLAG_NO_CLICKS => 1.3,
        self::FLAG_DECAYING => 1.2,
        self::FLAG_WRONG_INTENT => 1.1,
        self::FLAG_AI_MODE => 1.0,
        self::FLAG_UNTARGETED => 0.9,
        self::FLAG_IGNORE => 0.1,
    ];

    public function __construct(
        private readonly SearchConsoleMeasurementService $gscService
    ) {}

    /**
     * Refix döngüsünü baştan sona çalıştırır:
     * Read -> Judge -> Fix & Prioritize -> Write (Drafts) -> Measure.
     *
     * @param bool $dryRun Veritabanına kayıt yazmadan sadece analiz eder.
     * @param bool $applyDrafts Untargeted fırsatlar için ContentOpportunity taslakları oluşturur.
     * @param int $limit Raporlanacak en iyi fırsat sayısı.
     * @param array<int, array{query: string, page?: string, impressions: int, clicks: int, position: float, ctr?: float}>|null $injectedRows Test veya mock satırları.
     * @return array<string, mixed>
     */
    public function runLoop(
        bool $dryRun = true,
        bool $applyDrafts = false,
        int $limit = 50,
        ?array $injectedRows = null
    ): array {
        // 1. READ: Veriyi al (Enjekte edilmiş veri, Canlı API veya Son Ölçüm Raporu)
        $currentRows = $injectedRows ?? $this->fetchLatestQueryRows();
        $previousRowsMap = $this->fetchPreviousQueriesMap();
        $inventory = $this->getContentInventory();

        if (empty($currentRows)) {
            return [
                'status' => 'no_data',
                'message' => 'Analiz edilecek Search Console verisi bulunamadı.',
                'total_queries_analyzed' => 0,
                'flag_counts' => $this->emptyFlagCounts(),
                'opportunities' => [],
            ];
        }

        // 2. JUDGE & FIX: Her sorgu ve sayfa çiftini 6 bayrakla sınıflandır
        $evaluated = [];
        $flagCounts = $this->emptyFlagCounts();

        foreach ($currentRows as $row) {
            $queryKey = mb_strtolower(trim($row['query']));
            $prevRow = $previousRowsMap[$queryKey] ?? null;

            $result = $this->classifyQueryRow($row, $prevRow, $inventory);
            $flag = $result['flag'];
            $flagCounts[$flag] = ($flagCounts[$flag] ?? 0) + 1;

            if ($flag !== self::FLAG_IGNORE) {
                $evaluated[] = $result;
            }
        }

        // Fırsat puanına göre yüksekten düşüğe sırala
        usort($evaluated, static fn (array $a, array $b): int => ($b['opportunity_score'] <=> $a['opportunity_score']));

        // 3. WRITE: Untargeted fırsatlar için taslak ContentOpportunity oluştur
        $draftsCreated = 0;
        if (! $dryRun && $applyDrafts) {
            $draftsCreated = $this->applyUntargetedDrafts($evaluated);
        }

        $topOpportunities = array_slice($evaluated, 0, $limit);

        $summary = [
            'status' => 'success',
            'observed_at' => now()->toIso8601String(),
            'total_queries_analyzed' => count($currentRows),
            'actionable_opportunities_count' => count($evaluated),
            'flag_counts' => $flagCounts,
            'drafts_created' => $draftsCreated,
            'top_opportunities' => $topOpportunities,
        ];

        // 4. MEASURE: Sonucu IntegrityReport olarak kaydet
        if (! $dryRun) {
            IntegrityReport::query()->create([
                'guard' => 'seo:refix-loop',
                'status' => 'success',
                'headline' => count($evaluated).' actionable SEO opportunities identified',
                'payload' => [
                    'total_queries' => count($currentRows),
                    'flag_counts' => $flagCounts,
                    'top_scores' => array_map(static fn (array $o): array => [
                        'query' => $o['query'],
                        'flag' => $o['flag'],
                        'score' => $o['opportunity_score'],
                        'position' => $o['position'],
                        'impressions' => $o['impressions'],
                    ], array_slice($topOpportunities, 0, 15)),
                    'observed_at' => now()->toIso8601String(),
                ],
            ]);
        }

        return $summary;
    }

    /**
     * Tek bir sorgu ve sayfa satırını Refix'in 6 stratejik bayrağına göre sınıflandırır.
     *
     * @param array{query: string, page?: string, impressions: int, clicks: int, position: float, ctr?: float} $row
     * @param array{query: string, clicks: int, impressions: int, position: float}|null $prevRow
     * @param array{recipe_slugs: list<string>, blog_slugs: list<string>, published_titles: list<string>} $inventory
     * @return array<string, mixed>
     */
    public function classifyQueryRow(
        array $row,
        ?array $prevRow = null,
        array $inventory = ['recipe_slugs' => [], 'blog_slugs' => [], 'published_titles' => []]
    ): array {
        $query = trim($row['query']);
        $page = trim((string) ($row['page'] ?? ''));
        $impressions = (int) $row['impressions'];
        $clicks = (int) $row['clicks'];
        $position = (float) $row['position'];
        $ctr = isset($row['ctr']) && $row['ctr'] !== null
            ? (float) $row['ctr']
            : ($impressions > 0 ? $clicks / $impressions : 0.0);

        $words = array_values(array_filter(preg_split('/\s+/u', mb_strtolower($query)) ?: []));
        $wordCount = count($words);
        $isBrand = (bool) preg_match('/house\s*chef/i', $query);

        // Parametre eşikleri
        $almostThereMin = (float) config('seo.refix.almost_there_min_pos', 3.0);
        $almostThereMax = (float) config('seo.refix.almost_there_max_pos', 20.0);
        $noClicksMinImp = (int) config('seo.refix.no_clicks_min_impressions', 20);
        $noClicksMaxCtr = (float) config('seo.refix.no_clicks_max_ctr', 0.005);
        $decayingDropPct = (float) config('seo.refix.decaying_drop_pct', 30.0);
        $aiModeMinWords = (int) config('seo.refix.ai_mode_min_words', 7);

        // 1. DECAYING KONTROLÜ (Tıklama kaybı WoW >= 30%)
        $isDecaying = false;
        $decayPct = 0.0;
        if ($prevRow !== null && ($prevRow['clicks'] ?? 0) >= 2) {
            $prevClicks = (int) $prevRow['clicks'];
            if ($clicks < $prevClicks) {
                $dropPct = (($prevClicks - $clicks) / $prevClicks) * 100.0;
                if ($dropPct >= $decayingDropPct) {
                    $isDecaying = true;
                    $decayPct = round($dropPct, 1);
                }
            }
        }

        // 2. AI MODE KONTROLÜ (7+ kelimelik konuşma / LLM arama dili)
        $isAiMode = ($wordCount >= $aiModeMinWords);

        // 3. NO CLICKS KONTROLÜ (Yüksek gösterim, sıfır veya aşırı düşük CTR)
        $isNoClicks = ($impressions >= $noClicksMinImp && ($clicks === 0 || $ctr < $noClicksMaxCtr));

        // 4. ALMOST THERE KONTROLÜ (Pozisyon 3.0 - 20.0, non-brand)
        $isAlmostThere = (! $isBrand && $position >= $almostThereMin && $position <= $almostThereMax);

        // 5. UNTARGETED & WRONG INTENT KONTROLÜ
        $isListingOrHome = $this->isListingOrHomePage($page);
        $hasDedicatedTarget = $this->matchesInventory($query, $inventory);
        $isRecipeQuery = $this->isRecipeSpecificQuery($query);
        $isCommercialQuery = $this->isCommercialQuery($query);

        $isWrongIntent = false;
        if ($page !== '' && $isListingOrHome && ($isRecipeQuery || $hasDedicatedTarget)) {
            // Özel hedefi olan veya belirli bir tarif sorgusu kategori/liste/anasayfaya düşüyor
            $isWrongIntent = true;
        } elseif ($page !== '' && ! $isListingOrHome && $isCommercialQuery && ! str_contains($page, 'catering') && ! str_contains($page, 'chef')) {
            // Ticari şef/catering sorgusu dönüşüm widget'ı olmayan blog yazısına düşüyor
            $isWrongIntent = true;
        }

        // Untargeted: Sitede bu konuyu hedefleyen hiçbir sayfa yokken aramalarda görünmesi
        $isUntargeted = (! $hasDedicatedTarget && ($isListingOrHome || $page === '' || ! $this->pageMatchesQuery($page, $query)));

        // Karar Ağacı (Refix Stratejik Sıralama)
        $flag = self::FLAG_IGNORE;
        $action = 'Gözlemle ve performansını izle.';
        $recommendationType = 'monitor';

        if ($isDecaying) {
            $flag = self::FLAG_DECAYING;
            $action = sprintf('Tıklamalar %% %s düştü: İçeriğe 2026 güncel adımları ekleyin, yayın tarihini tazeleyin ve IndexNow bildirin.', $decayPct);
            $recommendationType = 'refresh_freshness';
        } elseif ($isAiMode) {
            $flag = self::FLAG_AI_MODE;
            $action = '7+ kelimelik konuşma dili sorgusu: AI Overview ve LLM alıntıları için tepeye 40-60 kelimelik özet cevap kutusu ve FAQ ekleyin.';
            $recommendationType = 'add_direct_answer_faq';
        } elseif ($isUntargeted) {
            $flag = self::FLAG_UNTARGETED;
            $action = 'Google bu konuda siteyi sıralıyor ancak özel hedef sayfası yok: Yeni bir taslak tarif veya rehber oluşturun.';
            $recommendationType = 'create_draft_content';
        } elseif ($isWrongIntent) {
            $flag = self::FLAG_WRONG_INTENT;
            $action = 'Arama niyeti ile sayfa tipi uyuşmuyor: Sayfa içine net CTA kutusu ekleyin veya doğru tekil sayfaya iç link verin.';
            $recommendationType = 'align_intent';
        } elseif ($isNoClicks) {
            $flag = self::FLAG_NO_CLICKS;
            $action = sprintf('Gösterim yüksek (%d), CTR düşük (%%%s): Meta başlığı merak uyandırıcı CTR kancası ile yenileyin.', $impressions, round($ctr * 100, 2));
            $recommendationType = 'rewrite_meta';
        } elseif ($isAlmostThere) {
            $flag = self::FLAG_ALMOST_THERE;
            $action = sprintf('Pozisyon %.1f (Sayfa 1-2 eşiği): İlk 3 sıraya sıçramak için içeriği derinleştirin, eksik alt başlıkları ve iç linkleri ekleyin.', $position);
            $recommendationType = 'enrich_content';
        } elseif ($position <= 2.0 && $clicks > 0) {
            $flag = self::FLAG_IGNORE;
            $action = 'Sıra 1-2: Pozisyon mükemmel, koruma modunda kal.';
            $recommendationType = 'hold';
        }

        // Fırsat Puanı Formülü: (Impressions * 10) / max(1.0, Position) * FlagWeight
        $weight = self::FLAG_WEIGHTS[$flag] ?? 1.0;
        $baseScore = ($impressions * 10.0) / max(1.0, $position);
        $opportunityScore = round($baseScore * $weight, 1);

        $fixPlan = $this->generateFixPlan($query, $page, $recommendationType, $flag);

        return [
            'query' => $query,
            'page' => $page,
            'impressions' => $impressions,
            'clicks' => $clicks,
            'position' => $position,
            'ctr' => round($ctr, 4),
            'word_count' => $wordCount,
            'is_brand' => $isBrand,
            'flag' => $flag,
            'weight' => $weight,
            'opportunity_score' => $opportunityScore,
            'recommended_action' => $action,
            'fix_plan' => $fixPlan,
        ];
    }

    /**
     * Fırsata özel somut iyileştirme planı (Başlık, Açıklama, Schema ve Eylem adımları) üretir.
     *
     * @return array<string, mixed>
     */
    public function generateFixPlan(string $query, string $page, string $type, string $flag): array
    {
        $cleanQuery = Str::title($query);

        return match ($type) {
            'rewrite_meta' => [
                'type' => 'rewrite_meta',
                'proposed_meta_title' => sprintf('%s Nasıl Yapılır? (2026 Tam Ölçülü Tarif)', $cleanQuery),
                'proposed_meta_description' => sprintf('%s arayanlar için püf noktaları, adım adım hazırlanışı ve orijinal Kıbrıs lezzeti. Hemen tıkla, keşfet!', $cleanQuery),
                'schema_suggestion' => 'HowTo / Recipe Rich Snippet ekle, pişirme süresi ve kalori bilgilerini zenginleştir.',
            ],
            'enrich_content' => [
                'type' => 'enrich_content',
                'target_h2' => sprintf('%s İçin Bilinmesi Gereken Püf Noktaları', $cleanQuery),
                'internal_link_anchor' => $cleanQuery,
                'depth_instruction' => 'Sayfaya 150+ kelimelik derinleştirici alt başlık ekleyin, malzemelerin yerel alternatiflerini listeleyin.',
            ],
            'add_direct_answer_faq' => [
                'type' => 'add_direct_answer_faq',
                'tldr_box' => sprintf('%s sorusunun net ve doğrudan cevabı: En doğru oran ve adımlarla Kıbrıs mutfağındaki pratik hazırlanışı.', $cleanQuery),
                'faq_schema' => [
                    'question' => sprintf('%s nedir ve nasıl uygulanır?', $cleanQuery),
                    'answer' => sprintf('%s hakkında bilinmesi gereken temel detaylar, süre ve ölçü oranları.', $cleanQuery),
                ],
            ],
            'create_draft_content' => [
                'type' => 'create_draft_content',
                'target_slug' => Str::slug($query),
                'content_type' => $this->isRecipeSpecificQuery($query) ? 'recipe' : 'blog_post',
                'status' => 'draft',
                'publication_policy' => 'draft_only_human_review_required',
            ],
            'align_intent' => [
                'type' => 'align_intent',
                'recommendation' => 'Kullanıcı liste veya anasayfada kaybolmasın: Sayfa üstüne öne çıkan arama kartı ve filtre yönlendirmesi ekleyin.',
            ],
            'refresh_freshness' => [
                'type' => 'refresh_freshness',
                'recommendation' => 'İçeriği 2026 güncel adımları ile tazeleyin, yayın/güncelleme tarihini güncelleyin ve IndexNow ile Google & Bing arama motorlarına bildirin.',
            ],
            default => [
                'type' => 'monitor',
                'recommendation' => 'Mevcut sıralamayı ve CTR dalgalanmalarını izlemeye devam edin.',
            ],
        };
    }

    /**
     * Untargeted fırsatlar için ContentOpportunity tablosunda taslak kayıtları açar.
     *
     * @param list<array<string, mixed>> $evaluatedOpportunities
     */
    private function applyUntargetedDrafts(array $evaluatedOpportunities): int
    {
        $created = 0;

        foreach ($evaluatedOpportunities as $item) {
            if ($item['flag'] !== self::FLAG_UNTARGETED) {
                continue;
            }

            $query = (string) $item['query'];
            $normalizedKey = 'refix-untargeted-'.hash('sha256', mb_strtolower($query));

            $opportunity = ContentOpportunity::query()->firstOrCreate(
                ['normalized_key' => $normalizedKey],
                [
                    'kind' => 'refix_loop_draft',
                    'title' => sprintf('Refix: "%s" için yeni hedef sayfa taslağı', $query),
                    'search_intent' => $this->isCommercialQuery($query) ? 'commercial' : 'informational',
                    'topic_cluster' => 'Refix Search Console Loop',
                    'priority' => min(100, (int) round(($item['opportunity_score'] ?? 50) / 2)),
                    'risk_level' => 'low',
                    'status' => 'open',
                    'evidence' => [
                        'source' => 'refix_seo_bot',
                        'flag' => self::FLAG_UNTARGETED,
                        'query' => $query,
                        'impressions' => $item['impressions'],
                        'clicks' => $item['clicks'],
                        'position' => $item['position'],
                        'score' => $item['opportunity_score'],
                        'suggested_fix' => $item['fix_plan'],
                        'delivery_policy' => 'draft_only',
                        'publication_requires_human_review' => true,
                    ],
                ]
            );

            if ($opportunity->wasRecentlyCreated) {
                $created++;
            }
        }

        return $created;
    }

    /**
     * Search Console'dan en güncel sorgu satırlarını çeker.
     *
     * @return list<array{query: string, page?: string, impressions: int, clicks: int, position: float, ctr?: float}>
     */
    public function fetchLatestQueryRows(int $limit = 500): array
    {
        // 1. Canlı API bağlantısı varsa ve yapılandırılmışsa query+page kombinasyonunu çekmeyi dene
        if (config('seo.search_console.enabled') && $this->gscService->configured()) {
            try {
                $liveRows = $this->fetchLiveQueryPageRows(28, $limit);
                if (! empty($liveRows)) {
                    return $liveRows;
                }
            } catch (Throwable) {
                // API hatasında bir alttaki rapor yedeğine geç
            }
        }

        // 2. Veritabanındaki en son IntegrityReport GSC ölçümünü kullan
        $report = IntegrityReport::query()
            ->where('guard', 'seo:search-console-measurement')
            ->where('status', 'success')
            ->orderByDesc('payload->end_date')
            ->latest('id')
            ->first();

        if ($report === null || ! is_array($report->payload)) {
            return [];
        }

        $queries = (array) ($report->payload['queries'] ?? []);

        return array_values(array_map(static fn (array $q): array => [
            'query' => (string) ($q['query'] ?? ''),
            'page' => (string) ($q['page'] ?? ''),
            'impressions' => (int) ($q['impressions'] ?? 0),
            'clicks' => (int) ($q['clicks'] ?? 0),
            'position' => (float) ($q['position'] ?? 0.0),
            'ctr' => ($q['impressions'] ?? 0) > 0 ? ((int) ($q['clicks'] ?? 0)) / ((int) $q['impressions']) : 0.0,
        ], $queries));
    }

    /**
     * Doğrudan Google Search Console API'sinden query+page boyutlarını çeker.
     *
     * @return list<array{query: string, page: string, impressions: int, clicks: int, position: float, ctr: float}>
     */
    public function fetchLiveQueryPageRows(int $days = 28, int $limit = 500): array
    {
        $tokenRes = Http::asForm()->withoutRedirecting()->connectTimeout(5)->timeout(10)->post('https://oauth2.googleapis.com/token', [
            'client_id' => config('seo.search_console.client_id'),
            'client_secret' => config('seo.search_console.client_secret'),
            'refresh_token' => config('seo.search_console.refresh_token'),
            'grant_type' => 'refresh_token',
        ]);

        if (! $tokenRes->successful() || ! is_string($tokenRes->json('access_token'))) {
            return [];
        }

        $accessToken = (string) $tokenRes->json('access_token');
        $url = 'https://www.googleapis.com/webmasters/v3/sites/'.rawurlencode($this->gscService->property()).'/searchAnalytics/query';

        $apiRes = Http::withToken($accessToken)
            ->withoutRedirecting()
            ->connectTimeout(5)
            ->timeout(15)
            ->post($url, [
                'startDate' => now()->subDays($days)->toDateString(),
                'endDate' => now()->subDays(2)->toDateString(),
                'dimensions' => ['query', 'page'],
                'rowLimit' => $limit,
            ]);

        if (! $apiRes->successful()) {
            return [];
        }

        $rows = (array) $apiRes->json('rows', []);

        return array_values(array_map(static fn (array $r): array => [
            'query' => (string) ($r['keys'][0] ?? ''),
            'page' => (string) ($r['keys'][1] ?? ''),
            'impressions' => (int) ($r['impressions'] ?? 0),
            'clicks' => (int) ($r['clicks'] ?? 0),
            'position' => round((float) ($r['position'] ?? 0.0), 1),
            'ctr' => (float) ($r['ctr'] ?? 0.0),
        ], $rows));
    }

    /**
     * Bir önceki ölçüm raporundaki sorguları anahtar-değer haritası olarak döndürür (WoW karşılaştırma için).
     *
     * @return array<string, array{query: string, clicks: int, impressions: int, position: float}>
     */
    public function fetchPreviousQueriesMap(): array
    {
        $reports = IntegrityReport::query()
            ->where('guard', 'seo:search-console-measurement')
            ->where('status', 'success')
            ->orderByDesc('payload->end_date')
            ->latest('id')
            ->take(2)
            ->get();

        if ($reports->count() < 2) {
            return [];
        }

        $previousQueries = (array) ($reports[1]->payload['queries'] ?? []);
        $map = [];

        foreach ($previousQueries as $q) {
            if (isset($q['query']) && is_string($q['query'])) {
                $key = mb_strtolower(trim($q['query']));
                $map[$key] = [
                    'query' => $q['query'],
                    'clicks' => (int) ($q['clicks'] ?? 0),
                    'impressions' => (int) ($q['impressions'] ?? 0),
                    'position' => (float) ($q['position'] ?? 0.0),
                ];
            }
        }

        return $map;
    }

    /**
     * Mevcut içerik envanterini (Tarif ve Blog başlıkları ile slug'ları) çeker.
     *
     * @return array{recipe_slugs: list<string>, blog_slugs: list<string>, published_titles: list<string>}
     */
    public function getContentInventory(): array
    {
        return Cache::remember('refix:content-inventory', 600, static function (): array {
            $recipeSlugs = Recipe::query()->pluck('slug')->all();
            $blogSlugs = BlogPost::query()->pluck('slug')->all();
            $recipeTitles = Recipe::query()->pluck('title')->all();
            $blogTitles = BlogPost::query()->pluck('title')->all();

            $titles = array_map(static fn (string $t): string => mb_strtolower(trim($t)), array_merge($recipeTitles, $blogTitles));

            return [
                'recipe_slugs' => array_map(static fn (string $s): string => mb_strtolower(trim($s)), $recipeSlugs),
                'blog_slugs' => array_map(static fn (string $s): string => mb_strtolower(trim($s)), $blogSlugs),
                'published_titles' => $titles,
            ];
        });
    }

    /**
     * Sorgunun mevcut içerik envanterinde hedeflenip hedeflenmediğini denetler.
     *
     * @param array{recipe_slugs: list<string>, blog_slugs: list<string>, published_titles: list<string>} $inventory
     */
    private function matchesInventory(string $query, array $inventory): bool
    {
        $normalized = Str::slug($query);
        $clean = mb_strtolower(trim($query));

        if (empty($inventory['recipe_slugs']) && empty($inventory['blog_slugs'])) {
            return false;
        }

        // Slug eşleşmesi
        foreach (array_merge($inventory['recipe_slugs'], $inventory['blog_slugs']) as $slug) {
            if ($slug === $normalized || str_contains($slug, $normalized) || str_contains($normalized, $slug)) {
                return true;
            }
        }

        // Başlık eşleşmesi
        foreach ($inventory['published_titles'] as $title) {
            if (str_contains($title, $clean) || str_contains($clean, $title)) {
                return true;
            }
        }

        return false;
    }

    private function isListingOrHomePage(string $page): bool
    {
        if ($page === '') {
            return false;
        }

        $path = (string) parse_url($page, PHP_URL_PATH);
        $path = rtrim($path, '/');

        return in_array($path, [
            '',
            '/tr',
            '/en',
            '/ru',
            '/de',
            '/pl',
            '/fa',
            '/pt',
            '/tarifler',
            '/tr/tarifler',
            '/en/tarifler',
            '/ru/tarifler',
            '/de/tarifler',
            '/pl/tarifler',
            '/blog',
            '/tr/blog',
        ], true);
    }

    private function pageMatchesQuery(string $page, string $query): bool
    {
        if ($page === '') {
            return false;
        }

        $path = (string) parse_url($page, PHP_URL_PATH);
        $slug = trim(basename($path));
        $normQuery = Str::slug($query);

        return $slug !== '' && ($slug === $normQuery || str_contains($slug, $normQuery) || str_contains($normQuery, $slug));
    }

    private function isRecipeSpecificQuery(string $query): bool
    {
        return (bool) preg_match('/(tarifi?|nasıl yapılır|kaç derece|malzemeleri|yapılışı|recipe|how to make)/iu', $query);
    }

    private function isCommercialQuery(string $query): bool
    {
        return (bool) preg_match('/(şef|özel şef|catering|fiyat|fiyatları|kirala|rezervasyon|private chef|hire chef)/iu', $query);
    }

    private function isConversationalQuery(string $query): bool
    {
        return (bool) preg_match('/(nasıl|nedir|kaç|hangi|ne zaman|how to|what is|can you|best way to)/iu', $query);
    }

    /**
     * @return array<string, int>
     */
    private function emptyFlagCounts(): array
    {
        return [
            self::FLAG_ALMOST_THERE => 0,
            self::FLAG_NO_CLICKS => 0,
            self::FLAG_DECAYING => 0,
            self::FLAG_UNTARGETED => 0,
            self::FLAG_WRONG_INTENT => 0,
            self::FLAG_AI_MODE => 0,
            self::FLAG_IGNORE => 0,
        ];
    }
}
