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
            'domain' => 'ktoz.org',
            'topic' => 'Kıbrıs Türk Otelciler Birliği Gastronomi & Ada Mirası',
            'target_url' => 'https://www.ktoz.org/',
            'asset_type' => 'blog',
            'asset_keyword' => 'kibris-mutfagina-baslangic-rehberi',
            'rationale' => 'Kıbrıs otellerinde sunulan yerel açık büfe ve ada lezzetleri için doğrulanmış gastronomi standart referansı.',
            'authority_score' => 78,
        ],
        [
            'domain' => 'kibrisrehberi.com',
            'topic' => 'Kuzey Kıbrıs Gezi & Yaşam Mutfak Kültürü',
            'target_url' => 'https://www.kibrisrehberi.com/kategori/yeme-icme',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-usulu-kleftiko',
            'rationale' => 'Adaya gelen yerli ve yabancı ziyaretçiler için otantik köy fırını kuzu tandır pişirme adımları rehberi.',
            'authority_score' => 72,
        ],
        [
            'domain' => 'emu.edu.tr',
            'topic' => 'Doğu Akdeniz Üniversitesi Gastronomi & Mutfak Sanatları',
            'target_url' => 'https://gastronomy.emu.edu.tr/',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-molohiya',
            'rationale' => 'Akademik gastronomi araştırmaları ve Kıbrıs mutfak mirası dersleri için gramajlı, adımlı birincil kaynak.',
            'authority_score' => 88,
        ],
        [
            'domain' => 'neu.edu.tr',
            'topic' => 'Yakın Doğu Üniversitesi Turizm ve Gastronomi Fakültesi',
            'target_url' => 'https://neu.edu.tr/faculties/tourism/',
            'asset_type' => 'blog',
            'asset_keyword' => 'kibris-sofralarinda-temel-malzemeler',
            'rationale' => 'Kıbrıs biyoçeşitliliği, yabani otlar ve ada peynirlerinin gastronomi eğitiminde akademik referans gösterilmesi.',
            'authority_score' => 86,
        ],
        [
            'domain' => 'ciu.edu.tr',
            'topic' => 'Uluslararası Kıbrıs Üniversitesi Gastronomi Bölümü',
            'target_url' => 'https://www.ciu.edu.tr/tr/akademik/fakulteler/uygulamali-bilimler-fakultesi/gastronomi-ve-mutfak-sanatlari',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-kolokas',
            'rationale' => 'Geleneksel Kolokas pişirme teknikleri ve kök sebze mirası için mutfak laboratuvarlarına referans kaynak.',
            'authority_score' => 84,
        ],
        [
            'domain' => 'final.edu.tr',
            'topic' => 'Uluslararası Final Üniversitesi Gastronomi ve Mutfak Sanatları',
            'target_url' => 'https://www.final.edu.tr/tr/programlar/gastronomi-ve-mutfak-sanatlari',
            'asset_type' => 'recipe',
            'asset_keyword' => 'hellimli-kibris-boregi',
            'rationale' => 'Geleneksel ada hamur işleri ve hellimli börek katlama teknikleri üzerine uygulamalı eğitim referansı.',
            'authority_score' => 77,
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
        [
            'domain' => '196flavors.com',
            'topic' => '196 Flavors World Culinary Heritage - Cyprus',
            'target_url' => 'https://www.196flavors.com/cyprus/',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-pilavuna',
            'rationale' => 'Paskalya ve bayram çöreği Pilavuna (Flaouna) için asırlık peynir harcı ve mahlep oranları dökümantasyonu.',
            'authority_score' => 76,
        ],
        [
            'domain' => 'eatyourworld.com',
            'topic' => 'Eat Your World Regional Foods - Cyprus',
            'target_url' => 'https://eatyourworld.com/destinations/europe/cyprus',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-seftali-kebabi',
            'rationale' => 'Doğu Akdeniz sokak lezzetleri ve otantik mangal kültürü için Şeftali Kebabı kaynak eşleşmesi.',
            'authority_score' => 74,
        ],
        [
            'domain' => 'olivetomato.com',
            'topic' => 'Olive Tomato Mediterranean Diet & Traditional Foods',
            'target_url' => 'https://www.olivetomato.com/',
            'asset_type' => 'recipe',
            'asset_keyword' => 'zeytinyagli-taze-fasulye',
            'rationale' => 'Akdeniz tipi soğuk sıkım zeytinyağlı sebze yemekleri ve ada pişirme teknikleri için besin odaklı referans.',
            'authority_score' => 73,
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
            'domain' => 'in-cyprus.philenews.com',
            'topic' => 'In-Cyprus Food & Culture Living Guide',
            'target_url' => 'https://in-cyprus.philenews.com/lifestyle/food-drink/',
            'asset_type' => 'blog',
            'asset_keyword' => 'geleneksel-kibris-kebabi',
            'rationale' => 'Ada genelinde kebap teknikleri ve geleneksel odun ateşi pişirme kültürü üzerine kapsamlı İngilizce kaynak.',
            'authority_score' => 81,
        ],
        [
            'domain' => 'cyprusscene.com',
            'topic' => 'Cyprus Scene North Cyprus Culture & Expat Living',
            'target_url' => 'https://cyprusscene.com/category/culture/',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-pirohu',
            'rationale' => 'Kıbrıs köylerine özgü taze nor ve kuru nane dolgulu ev makarnası Pirohu için adım adım rehber.',
            'authority_score' => 71,
        ],
        [
            'domain' => 'cyprusbutterfly.com.cy',
            'topic' => 'Cyprus Butterfly Lifestyle, Travel & Culinary Notes',
            'target_url' => 'https://cyprusbutterfly.com.cy/travel',
            'asset_type' => 'blog',
            'asset_keyword' => 'ev-olculeri-rehberi',
            'rationale' => 'Akdeniz ve Kıbrıs tariflerini evde uygulayan yabancı okurlar için su bardağı ve gramaj dönüşüm cetveli.',
            'authority_score' => 69,
        ],
        [
            'domain' => 'financialmirror.com',
            'topic' => 'Cyprus Financial Mirror Hospitality & Tourism Sector',
            'target_url' => 'https://www.financialmirror.com/category/cyprus/business/tourism-hospitality/',
            'asset_type' => 'blog',
            'asset_keyword' => 'private-chef-cyprus',
            'rationale' => 'Kıbrıs otelcilik ve özel mutfak hizmetleri sektörü için profesyonel şef standartları referansı.',
            'authority_score' => 79,
        ],
        [
            'domain' => 'merithotels.com',
            'topic' => 'Merit International Hotels & Gourmet Kitchens',
            'target_url' => 'https://www.merithotels.com/gastronomi',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-usulu-kleftiko',
            'rationale' => 'Lüks ada otellerinin alakart restoranlarında Kıbrıs mirası fırın kebabı sunumları için otantik referans.',
            'authority_score' => 83,
        ],
        [
            'domain' => 'kayahotels.com',
            'topic' => 'Kaya Artemis Resort Gastronomi Ekibi',
            'target_url' => 'https://www.kayahotels.com/oteller/kaya-artemis-resort-casino',
            'asset_type' => 'blog',
            'asset_keyword' => 'kibris-mezeleriyle-pratik',
            'rationale' => 'Karpaz ve Bafra bölgesinde ada mezeleri ve Akdeniz soğuk tabakları için mutfak referansı.',
            'authority_score' => 80,
        ],
        [
            'domain' => 'limakhotels.com',
            'topic' => 'Limak Cyprus Deluxe Resort Mutfak Kültürü',
            'target_url' => 'https://www.limakhotels.com/limak-cyprus-deluxe-hotel',
            'asset_type' => 'recipe',
            'asset_keyword' => 'kibris-seftali-kebabi',
            'rationale' => 'Otantik Türk ve Kıbrıs mutfağı lezzet günleri için doğrulanmış reçete kaynağı.',
            'authority_score' => 81,
        ],
        [
            'domain' => 'cratospremium.com',
            'topic' => 'Cratos Premium Gourmet Dining & Culinary Team',
            'target_url' => 'https://www.cratospremium.com/dining',
            'asset_type' => 'blog',
            'asset_keyword' => 'private-chef-cyprus',
            'rationale' => 'Girne sahilinde özel villa catering ve fine-dining mutfak standartları rehberi.',
            'authority_score' => 77,
        ],
        [
            'domain' => 'arkincolonyhotel.com',
            'topic' => 'The Arkin Colony Hotel Girne Mutfak Ekibi',
            'target_url' => 'https://arkincolonyhotel.com/restaurants/',
            'asset_type' => 'recipe',
            'asset_keyword' => 'magarina-bulli',
            'rationale' => 'Girne tarihi merkezinde geleneksel tavuklu köy makarnası ve rendelenmiş hellim mirası sunumu.',
            'authority_score' => 75,
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
