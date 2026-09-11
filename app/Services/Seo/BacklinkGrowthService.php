<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\BlogPost;
use App\Models\ContentOpportunity;
use App\Models\Recipe;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class BacklinkGrowthService
{
    /** @var list<array{domain: string, topic: string, target_url: string, asset_type: string, asset_keyword: string, rationale: string, authority_score: int}> */
    private const TARGET_PROSPECTS = [
        [
            'domain' => 'visitncy.com',
            'topic' => 'Kıbrıs Gastronomisi ve Geleneksel Mutfak',
            'target_url' => 'https://www.visitncy.com/discover-category/gastronomy/',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-seftali-kebabi',
            'rationale' => 'Resmi turizm portalının gastronomi sayfasında Şeftali Kebabı ve Fırın Kebabı için gram ölçülü, doğrulanmış tarif kaynağı sunar.',
            'authority_score' => 85,
        ],
        [
            'domain' => 'visitcyprus.com',
            'topic' => 'Geleneksel Kıbrıs Hellimi ve Ada Ürünleri',
            'target_url' => 'https://www.visitcyprus.com/discover-cyprus/food-drink/local-produce/halloumi-cheese/',
            'asset_type' => 'blog',
            'asset_keyword' => 'kibris-sofralarinda-temel-malzemeler',
            'rationale' => 'Geleneksel hellim üretimi ve adanın temel malzemelerini anlatan editoryal rehberimiz için mükemmel birincil kaynak eşleşmesi.',
            'authority_score' => 90,
        ],
        [
            'domain' => 'ktto.net',
            'topic' => 'Kıbrıs Türk Ticaret Odası Yerel Üretici Ağı',
            'target_url' => 'https://www.ktto.net/iletisim/',
            'asset_type' => 'blog',
            'asset_keyword' => 'kibris-mutfagina-baslangic-rehberi',
            'rationale' => 'Yerel keçiboynuzu, hellim ve zeytinyağı üreticilerini kaynak gösteren editoryal Kıbrıs mutfağı başlangıç rehberimiz.',
            'authority_score' => 75,
        ],
        [
            'domain' => 'slowfoodcyprus.org',
            'topic' => 'Slow Food Kıbrıs & Nuh’un Ambarı Miras Lezzetleri',
            'target_url' => 'https://www.slowfood.com/our-network/cyprus/',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-kolokas',
            'rationale' => 'Adanın kadim kök sebzesi Kolokas ve yerel bakliyat mirası için geleneksel pişirme yöntemlerini belgeler.',
            'authority_score' => 80,
        ],
        [
            'domain' => 'cyprus-mail.com',
            'topic' => 'Cyprus Mail Living & Mediterranean Culinary Heritage',
            'target_url' => 'https://cyprus-mail.com/category/living/food-drink/',
            'asset_type' => 'blog',
            'asset_keyword' => 'private-chef-cyprus',
            'rationale' => 'Kıbrıs gastronomi kültürü ve özel şef deneyimleri rehberi için uluslararası expat ve turist kitlesine yönelik editoryal kaynak.',
            'authority_score' => 82,
        ],
        [
            'domain' => 'tasteatlas.com',
            'topic' => 'TasteAtlas Authentic Cypriot Cuisine Database',
            'target_url' => 'https://www.tasteatlas.com/cyprus',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-usulu-kleftiko',
            'rationale' => 'Kleftiko (Kuzu Fırın Tandıra) ait gerçek ada pişirme teknikleri, saatlik ısı grafiği ve besin değerleri verisi sağlar.',
            'authority_score' => 88,
        ],
        [
            'domain' => 'mediterraneanliving.com',
            'topic' => 'Mediterranean Diet & Healthy Cypriot Home Cooking',
            'target_url' => 'https://www.mediterraneanliving.com/',
            'asset_type' => 'blog',
            'asset_keyword' => 'kibris-mezeleriyle-pratik',
            'rationale' => 'Zeytinyağlı taze mezeler, otlar ve sebzeli Akdeniz tabakları için kanıtlanmış porsiyon ve kalori tabloları sunar.',
            'authority_score' => 70,
        ],
    ];

    /**
     * Identifies, enriches, and stores high-authority backlink candidates in content_opportunities.
     *
     * @return array{created: int, updated: int, prospects: list<array<string, mixed>>}
     */
    public function discoverAndEnrich(): array
    {
        $created = 0;
        $updated = 0;
        $results = [];

        foreach (self::TARGET_PROSPECTS as $prospect) {
            $key = Str::slug('backlink-' . $prospect['domain'] . '-' . $prospect['asset_keyword']);

            $opportunity = ContentOpportunity::query()->firstOrNew(['normalized_key' => $key]);

            if ($opportunity->exists && ($opportunity->status !== 'open' || $opportunity->reviewed_at !== null)) {
                continue;
            }

            $matchedAsset = $this->resolveMatchedAsset($prospect['asset_type'], $prospect['asset_keyword']);
            $assetUrl = $matchedAsset['url'];
            $assetTitle = $matchedAsset['title'];

            $pitchTr = sprintf(
                "Merhaba, %s sitenizde %s üzerine yayınlanan yazınızı ilgiyle inceledik. " .
                "House Chef Cyprus platformumuzda konuyla ilgili bağımsız mutfak ekibimizce doğrulanmış '%s' kaynağımız (%s) yer almaktadır. " .
                "Okuyucularınızın geleneksel gramaj, besin değeri ve ada pişirme tekniklerine ulaşması için kaynak olarak değerlendirebilirsiniz.",
                $prospect['domain'],
                $prospect['topic'],
                $assetTitle,
                $assetUrl
            );

            $pitchEn = sprintf(
                "Dear %s editorial team, we recently explored your culinary feature on %s. " .
                "At House Chef Cyprus, our culinary editors verified an in-depth reference: '%s' (%s), " .
                "complete with verified metric measurements, nutritional data, and cultural heritage notes. " .
                "We believe this serves as an authentic, high-value reference for your international readers.",
                $prospect['domain'],
                $prospect['topic'],
                $assetTitle,
                $assetUrl
            );

            $evidence = [
                'target_domain' => $prospect['domain'],
                'target_url' => $prospect['target_url'],
                'topic' => $prospect['topic'],
                'authority_score' => $prospect['authority_score'],
                'rationale' => $prospect['rationale'],
                'matched_asset_title' => $assetTitle,
                'matched_asset_url' => $assetUrl,
                'pitch_draft_tr' => $pitchTr,
                'pitch_draft_en' => $pitchEn,
                'outreach_status' => 'draft_pending_human_review',
                'delivery_policy' => 'draft_only',
                'publication_requires_human_review' => true,
                'collected_at' => now()->toIso8601String(),
            ];

            $wasCreated = ! $opportunity->exists;

            $opportunity->fill([
                'kind' => 'backlink_prospect',
                'title' => "Backlink Fırsatı: {$prospect['domain']} ({$prospect['topic']})",
                'search_intent' => 'commercial',
                'topic_cluster' => 'Dış Bağlantı & Dijital PR',
                'evidence' => $evidence,
                'risk_level' => 'low',
                'priority' => $prospect['authority_score'],
                'status' => 'open',
            ]);

            $opportunity->save();

            $wasCreated ? $created++ : $updated++;

            $results[] = [
                'domain' => $prospect['domain'],
                'asset_title' => $assetTitle,
                'asset_url' => $assetUrl,
                'priority' => $prospect['authority_score'],
            ];
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'prospects' => $results,
        ];
    }

    /**
     * Pings WebSub / RSS feed hubs to announce fresh content syndication.
     *
     * @return array{pinged: bool, hub_status: int, feeds: list<string>}
     */
    public function pingSyndicationHubs(): array
    {
        $feeds = [
            route('feed'),
            route('feed.en'),
        ];

        $hubs = [
            'https://pubsubhubbub.appspot.com/',
        ];

        $success = true;
        $lastStatus = 200;

        foreach ($hubs as $hub) {
            foreach ($feeds as $feed) {
                try {
                    $response = Http::timeout(5)
                        ->asForm()
                        ->post($hub, [
                            'hub.mode' => 'publish',
                            'hub.url' => $feed,
                        ]);

                    $lastStatus = $response->status();
                    if (! $response->successful() && $lastStatus !== 204 && $lastStatus !== 200) {
                        $success = false;
                    }
                } catch (\Throwable $e) {
                    Log::debug('WebSub hub ping skipped: ' . $e->getMessage());
                    $success = false;
                    $lastStatus = 0;
                }
            }
        }

        return [
            'pinged' => $success,
            'hub_status' => $lastStatus,
            'feeds' => $feeds,
        ];
    }

    /**
     * @return array{title: string, url: string}
     */
    private function resolveMatchedAsset(string $type, string $keyword): array
    {
        if ($type === 'recipe') {
            $recipe = Recipe::query()
                ->where('status', 'published')
                ->where('slug', 'like', "%{$keyword}%")
                ->first();

            if ($recipe) {
                return [
                    'title' => $recipe->title,
                    'url' => route('recipes.show', $recipe),
                ];
            }
        } else {
            $blog = BlogPost::query()
                ->publiclyVisible()
                ->where('slug', 'like', "%{$keyword}%")
                ->first();

            if ($blog) {
                return [
                    'title' => $blog->title,
                    'url' => route('blog.show', $blog),
                ];
            }
        }

        return [
            'title' => 'House Chef Cyprus - Doğrulanmış Kıbrıs Tarifleri',
            'url' => route('cuisines.show', 'kibris-mutfagi'),
        ];
    }
}
