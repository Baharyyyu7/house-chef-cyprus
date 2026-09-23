<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Models\Recipe;
use App\Models\SlugRedirect;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Google Search Console'da 404 veren veya taşınmış/birleştirilmiş tüm eski
 * adresleri kalıcı olarak 301 yönlendirmesiyle canlı eşlerine bağlar.
 */
final class SeoHealRedirects extends Command
{
    protected $signature = 'seo:heal-redirects {--apply : Değişiklikleri veritabanına yaz. Varsayılan yalnız rapordur.}';

    protected $description = 'GSC 404 hatalarını ve geo-2026 / taslak blog yönlendirmelerini onarır.';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $this->components->info('GSC 404 ve Eski Slug Yönlendirme Onarımı Başlatılıyor...');

        $healedGeo = 0;
        $healedDraftBlogs = 0;
        $healedRecipes = 0;
        $healedSpecific = 0;

        // 1. 100 Geo Blog (geo-2026-...) Yönlendirmeleri
        $geoSeedsPath = database_path('seeders/100_geo_blogs_seeds.json');
        if (file_exists($geoSeedsPath)) {
            $geoSeeds = json_decode((string) file_get_contents($geoSeedsPath), true) ?: [];
            foreach ($geoSeeds as $seed) {
                $geoSlug = (string) ($seed['slug'] ?? '');
                $title = (string) ($seed['title'] ?? '');

                if ($geoSlug === '') {
                    continue;
                }

                $post = BlogPost::query()->publiclyVisible()->where('title', $title)->limit(2)->get();
                if ($post->count() !== 1) {
                    $cleanSlug = str_replace('geo-2026-', '', $geoSlug);
                    $post = BlogPost::query()->publiclyVisible()
                        ->where(fn ($query) => $query->where('slug', $cleanSlug)->orWhere('slug', 'like', $cleanSlug . '-%'))
                        ->limit(2)
                        ->get();
                }
                $post = $post->count() === 1 ? $post->first() : null;

                if ($post !== null) {
                    $this->registerRedirect(BlogPost::class, $geoSlug, $post->id, $apply);
                    $healedGeo++;

                    // URL encoded hali (örn: %C4%B1 vs ı)
                    $rawUrlEncoded = rawurlencode($geoSlug);
                    if ($rawUrlEncoded !== $geoSlug) {
                        $this->registerRedirect(BlogPost::class, $rawUrlEncoded, $post->id, $apply);
                    }
                }
            }
        }

        // 2. Taslağa Çekilmiş Yinelenen Blog Yazıları -> Yayındaki Eşine Bağlama
        $draftPosts = BlogPost::query()->where('status', 'draft')->get(['id', 'slug', 'title', 'blog_category_id']);
        foreach ($draftPosts as $draft) {
            // A) Tam başlık eşleşmesi
            $twin = BlogPost::query()->publiclyVisible()->where('title', $draft->title)->where('id', '!=', $draft->id)->first();

            // B) Sonek temizlenmiş kök slug eşleşmesi
            if ($twin === null) {
                $rootSlug = (string) preg_replace('/-(?:taslak-inceleme-)?[A-Za-z0-9]{5}$/', '', (string) $draft->slug);
                if ($rootSlug !== '' && $rootSlug !== $draft->slug) {
                    $twin = BlogPost::query()->publiclyVisible()
                        ->where(fn ($q) => $q->where('slug', $rootSlug)->orWhere('slug', 'like', $rootSlug . '-%'))
                        ->first();
                }
            }

            if ($twin !== null) {
                $this->registerRedirect(BlogPost::class, (string) $draft->slug, $twin->id, $apply);
                $this->registerRedirect(BlogPost::class, rawurldecode((string) $draft->slug), $twin->id, $apply);
                $healedDraftBlogs++;
            }
        }

        // 3. Bilinen Özel GSC 404 URL Eşleştirmeleri
        $specificBlogMappings = [
            'private-chef-cyprus-kibrista-ozel-sef-ve-mutfak-deneyimi-rehberi' => 'private-chef-cyprus-kibrista-ozel-sef-ve-mutfak-deneyimi-rehberi-BH3DZ',
            'akdeniz-diyeti-ve-omur-uzatan-beslenme-prensipleri-haftalik-ornek-menu' => 'akdeniz-diyeti-nedir-omur-uzatan-beslenme-prensipleri-ve-haftalik-ornek-menu-0XP2B',
            'firin-kac-dakikada-isinir-on-isitma-sureleri-ve-puf-noktali' => 'firin-kac-dakikada-isinir-on-isitma-sureleri-ve-puf-noktalari',
            'firin-kac-dakikada-isinir-on-isitma-sureleri-ve-puf-noktalari-3333' => 'firin-kac-dakikada-isinir-on-isitma-sureleri-ve-puf-noktalari',
            'kibris-usulu-kuru-bakla-favasi-ve-kirmizi-sogan-mezesi-EGCGT' => 'kibris-usulu-kuru-bakla-favasi-ve-kirmizi-sogan-mezesi-EGCGT',
            'glutensiz-ekmek-ve-hamur-isi-puf-noktalari-kabarma-ve-doku-sirlari' => 'glutensiz-ekmek-ve-hamur-isi-puf-noktalari-kabarma-ve-doku-sirlari',
            'kemik-suyu-ve-dogal-kolajen-rehberi-jel-kivaminda-ilikli-kemik-suyu' => 'kemik-suyu-ve-dogal-kolajen-rehberi-jel-kivaminda-ilikli-kemik-suyu-X6HFA',
            'orijinal-lokanta-usulu-mercimek-corbasi-tarifi-ipeksi-kivamin-sirri-KZM3Q' => 'orijinal-lokanta-usulu-mercimek-corbasi-tarifi-ipeksi-kivamin-sirri-3Y13B',
            'akdeniz-levregi-ve-cipura-bugulama-sulu-ve-aromatik-balik-H08HA' => 'firinda-levrek-ve-cipura-bugulama-sebze-yataginda-kurumayan-sulu-balik-pisirme-sanati-SCCFX',
            'arpacik-soganli-kibris-usulu-agir-ateste-KVC3V' => 'kibris-ayvali-kuzu-yahni-tarifi-ve-et-meyve-uyumu',
            'asma-yapragina-sarili-sardalya-izgara-dumansiz-lezzet-14R74' => 'firinda-levrek-ve-cipura-bugulama-sebze-yataginda-kurumayan-sulu-balik-pisirme-sanati-SCCFX',
            'besparmak-daglarinda-defne-ve-mersin-yapragi-kullanimi-BRK3A' => 'kibris-mutfagi-rehberi',
            'citir-sigara-boregi-yag-cekmeyen-kizartma-yontemi-ZAKTG' => 'kibris-usulu-lalangi-peynirli-akitma-kibris-kahvaltilik-krebi-tarifi',
            'dipkarpaz-dogal-deniz-tuzu-ve-mutfakta-tuzlama-teknikleri-VHDTB' => 'kibris-mutfagi-rehberi',
            'dogal-yabani-semizotu-ve-suzme-koy-yogurdu-dengesi-W35X3' => 'kibris-meze-rehberi',
            'et-pisirme-sicakliklari-rare-medium-ve-well-done-bilimi' => 'et-marinasyonu-rehberi-etleri-lokum-gibi-yapan-marineler',
            'frenk-kimyonu-ve-kibris-koy-sucugunun-baharat-dengesi-Q95T1' => 'kibris-mutfagi-rehberi',
            'geleneksel-kibris-paluzesi-uzum-suyu-ve-DWH34' => 'kibris-kofter-ve-cevizli-sucuk-yapimi-uzum-sirasi-gelenegi',
            'guvecte-tereyagli-karides-sarimsak-ve-aci-1S77P' => 'girne-usulu-deniz-mahsulleri-makarna',
            'kıbrıs-meze-rehberi' => 'kibris-meze-rehberi',
            'kibris-koy-hellimi-yapimi-ve-geleneksel-salamura-sirlari-Y27SB' => 'kibris-hellim-peyniri-nasil-kizartilir-ve-saklanir-koy-usulu-rehber',
            'kibris-meze-kulturu-paylasmanin-lezzeti' => 'kibris-meze-rehberi',
            'kibris-ta-keci-ve-koyun-yetistiriciligi-ile-mutfak-baglantisi' => 'kibris-mutfagi-rehberi',
            'kirmizi-mercan-ve-barbun-baligi-citir-kizartma-sirlari-3WSX9' => 'balik-pisirme-teknikleri-firin-tava-izgara-hangi-balik-nasil-pisirilir',
            'kis-sofralari-icin-portakalli-zeytinyagli-pirasa-N03AB' => 'zeytinyagli-yaprak-sarma-puf-noktalari-dagilmayan-ve-parlak-sarma-sirri-KP91T',
            'koy-usulu-ebegumeci-ve-zeytinyagli-pazi-sarmasi-sirlari-MMDF4' => 'zeytinyagli-yaprak-sarma-puf-noktalari-dagilmayan-ve-parlak-sarma-sirri-KP91T',
            'koy-usulu-ilikli-kemik-suyu-12-saat-agir-kaynatma-QYWQP' => 'kemik-suyu-ve-dogal-kolajen-rehberi-jel-kivaminda-ilikli-kemik-suyu-X6HFA',
            'koz-patlicanli-meze-cesitleri-babaganus-ve-mutabbal-TY91J' => 'kibris-meze-rehberi',
            'lefke-hurma-agaclari-ve-geleneksel-hurmali-kibris-tatlilari-RC70R' => 'kibris-tatlisi-serbet-ve-krema-dengesi-agizda-eriyen-orijinal-ada-recetesi-HJHAK',
            'serdarli-ve-gecitkale-koylerinde-firinda-hellimli-WS987' => 'kibris-hellimli-ve-zeytinli-bitta-yapilisi-ada-ekmegi',
            'tahini-susam-ezmesinin-uretiminden-sofraya' => 'kibris-sicak-humus-paca-nasil-yapilir-firinda-tahinli-lezzet',
            'nohut-humustan-fasulye-koftesine-cok-yonlu-baklagil' => 'kibris-sicak-humus-paca-nasil-yapilir-firinda-tahinli-lezzet',
            'tavuk-tantuni-yaparken-etin-suyunu-koruma-yontemleri-8KENJ' => 'et-marinasyonu-rehberi-etleri-lokum-gibi-yapan-marineler',
            'yabani-citlembik-murt-taneli-kibris-koy-coregi-QE83K' => 'kibris-citlembikli-corek-ve-ekmek-yapimi-ada-gelenekleri',
            'zeytinyaginda-baharat-ve-sarimsak-dinlendirme-sanati-AXGJK' => 'zeytinyagi-secimi-ve-saklama-kosullari-soguk-sikim-asit-orani-ve-sahtelik-testi',
            'zeytinyagli-yemeklerde-asit-seker-ve-doku-dengesi-BB1SH' => 'zeytinyagli-enginar-dolmasi-ve-canak-enginar-kararmayan-pisirme-sanati',
            'geo-2026-cakistes-kirma-yesil-zeytin-hazirlama-puf-noktalari' => 'kibris-cakistes-kirma-yesil-zeytin-nasil-yapilir-tatlandirma-rehberi',
            'geo-2026-besparmak-daglarinda-defne-ve-mersin-yapragı' => 'kibris-mutfagi-rehberi',
            'geo-2026-dag-zahteri-ve-sumak-karisimi-kahvalti' => 'kibris-mutfagi-rehberi',
            'geo-2026-dipkarpaz-tuz-golu-ve-dogal-akdeniz-tuzu' => 'kibris-mutfagi-rehberi',
            'geo-2026-ebegumeci-ve-pazi-sarmasi-koy-usulu' => 'zeytinyagli-yaprak-sarma-puf-noktalari-dagilmayan-ve-parlak-sarma-sirri-KP91T',
            'geo-2026-frenk-kimyonu-ve-koy-sucugu-baharat-dengesi' => 'kibris-mutfagi-rehberi',
            'geo-2026-kibris-fava-bakla-ezmesi-karamelize-sogan' => 'kibris-usulu-kuru-bakla-favasi-ve-kirmizi-sogan-mezesi-EGCGT',
            'geo-2026-kibris-kaparisi-gabbar-tursusu-ve-kullanimi' => 'citir-citir-tursu-kurma-rehberi-kutur-kutur-salatalik-ve-lahana-tursusu-sirri-GJYQH',
            'geo-2026-kibris-paluzes-uzum-tatlisi-ve-badem-uyumu' => 'kibris-kofter-ve-cevizli-sucuk-yapimi-uzum-sirasi-gelenegi',
            'geo-2026-kibris-tatlisi-serbetli-ve-cevizli-kek-krema' => 'kibris-tatlisi-serbet-ve-krema-dengesi-agizda-eriyen-orijinal-ada-recetesi-HJHAK',
            'geo-2026-lefke-hurma-agaclari-ve-hurmali-tatlilar' => 'kibris-tatlisi-serbet-ve-krema-dengesi-agizda-eriyen-orijinal-ada-recetesi-HJHAK',
            'geo-2026-maroni-ve-mesarya-koy-ekmegi-pisirme' => 'evde-dogal-eksi-maya-nasil-yapilir-besleme-saklama-ve-ici-gozenekli-ekmek-sanati-46CG7',
            'geo-2026-serdarli-ve-gecitkale-hellimli-bitta-firin' => 'kibris-hellimli-ve-zeytinli-bitta-yapilisi-ada-ekmegi',
            'geo-2026-zeytinyagli-barbunya-pilaki-ipeksi-sos-sirri' => 'zeytinyagli-enginar-dolmasi-ve-canak-enginar-kararmayan-pisirme-sanati',
            'geo-2026-sele-zeytini-ve-siyah-zeytin-olgunlastirma' => 'kibris-cakistes-kirma-yesil-zeytin-nasil-yapilir-tatlandirma-rehberi',
            'ev-yapimi-peynir-hellim-lor-ve-tulum' => 'kibris-hellim-peyniri-nasil-kizartilir-ve-saklanir-koy-usulu-rehber',
            'geleneksel-kibris-corbalarinin-tarihi' => 'evde-probiyotik-kislik-tarhana-hamuru-nasil-yogrulur-ve-kurutulur-canli-kultur-rehberi-51CCW',
            'kibris-gecis-donemi-yemekleri-ramazan-paskalya-ve-noel-sofralari' => 'kibris-dugun-yemekleri',
            'kibris-mutfaginda-hellim-tarihce-uretim-ve-farkli-pisirme-yontemleri' => 'kibris-hellim-peyniri-nasil-kizartilir-ve-saklanir-koy-usulu-rehber',
            'kibris-ta-narenciye-limon-portakal-ve-greyfurt-kullanimi' => 'kibris-mutfagi-rehberi',
            'kis-sofrasinin-vazgecilmezi-kuru-baklagiller-ve-hazirlik-yontemleri' => 'kuru-bakliyatlar-buzdolabinda-saklanir-mi-guvelenmeyi-onleme-yontemleri-0QG4N',
            'evde-gercek-citir-sokak-simidi-nasil-yapilir-pekmezleme-ve-susam-sirri' => 'evde-dogal-eksi-maya-nasil-yapilir-besleme-saklama-ve-ici-gozenekli-ekmek-sanati-46CG7',
            'meyve-ve-sebzeleri-pestisitlerden-arindirma-rehberi-karbonat-ve-sirkeli-yikama-bilimi-QH5DK' => 'taze-sebzeleri-buzdolabinda-saklamak',
            'geo-2026-dana-yahni-ve-karamelize-arpacik-sogan' => 'kibris-ayvali-kuzu-yahni-tarifi-ve-et-meyve-uyumu',
            'sogan-karamelizasyonu-neden-ve-nasil' => 'sogan-karamelizasyonu-neden-ve-nasil',
        ];

        foreach ($specificBlogMappings as $oldSlug => $targetSlug) {
            $target = BlogPost::query()->publiclyVisible()->where('slug', $targetSlug)->first();
            if ($target === null) {
                $targets = BlogPost::query()->publiclyVisible()
                    ->where('slug', 'like', $targetSlug.'-%')
                    ->limit(2)
                    ->get();
                $target = $targets->count() === 1 ? $targets->first() : null;
            }
            if ($target !== null) {
                $this->registerRedirect(BlogPost::class, $oldSlug, $target->id, $apply);
                $this->registerRedirect(BlogPost::class, rawurldecode($oldSlug), $target->id, $apply);
                $this->registerRedirect(BlogPost::class, rawurlencode($oldSlug), $target->id, $apply);
                $healedSpecific++;
            }
        }

        // 4. Çapraz Model Eşleştirmeleri (Blog -> Tarif veya Tarif -> Blog)
        $crossModelMappings = [
            'kibris-usulu-pilavuna-nasil-yapilir-geleneksel-paskalya-ve-bayram-coregi' => [Recipe::class, 'kibris-usulu-flaouna'],
            'kibris-flaouna-pilavuna-ozel-peynirli-baharatli-corek-BS9JP' => [Recipe::class, 'kibris-usulu-flaouna'],
            'hakiki-haydari-yogun-suzme-yogurt-ve-nane-aromasi-5YRM0' => [Recipe::class, 'haydari'],
            'geo-2026-kibris-kleftiko-firin-kebabi-pisirme-rehberi' => [Recipe::class, 'kibris-usulu-kleftiko'],
            'geo-2026-kibris-molehiya-tavuklu-ve-kuzu-etli-pisirme' => [Recipe::class, 'kibris-usulu-molehiya-tarifi'],
            'geo-2026-tahinli-patlican-salatasi-mutabbal-ve-babaganus' => [Recipe::class, 'patlican-salatasi-babaganus'],
            'geo-2026-zeytinyagli-enginar-kalbi-ve-bakla-pilaki' => [Recipe::class, 'zeytinyagli-enginar'],
            'geo-2026-firin-tavuk-ve-baharatli-koy-patatesi' => [Recipe::class, 'firin-tavuk-baget'],
            'geo-2026-uzum-sirasi-kofter-ve-sucuk-yapimi-gelenegi' => [Recipe::class, 'kibris-macunu-cevizli-sucuk'],
            'kuzu-tandir-etini-lokum-gibi-yapan-dusuk-isi-kontrolu-BXVXS' => [Recipe::class, 'kibris-usulu-kleftiko'],
        ];

        foreach ($crossModelMappings as $oldSlug => [$targetModelClass, $targetSlug]) {
            if ($targetModelClass === Recipe::class) {
                $target = Recipe::query()->where('status', 'published')->where('verification_status', 'verified')->where('slug', $targetSlug)->first();
                if ($target === null) {
                    $targets = Recipe::query()->where('status', 'published')->where('verification_status', 'verified')
                        ->where('slug', 'like', $targetSlug.'-%')
                        ->limit(2)
                        ->get();
                    $target = $targets->count() === 1 ? $targets->first() : null;
                }
            } else {
                $target = BlogPost::query()->publiclyVisible()->where('slug', $targetSlug)->first();
                if ($target === null) {
                    $targets = BlogPost::query()->publiclyVisible()
                        ->where('slug', 'like', $targetSlug.'-%')
                        ->limit(2)
                        ->get();
                    $target = $targets->count() === 1 ? $targets->first() : null;
                }
            }
            if ($target !== null) {
                $this->registerRedirect($targetModelClass, $oldSlug, $target->id, $apply);
                $this->registerRedirect($targetModelClass, rawurldecode($oldSlug), $target->id, $apply);
                $this->registerRedirect($targetModelClass, rawurlencode($oldSlug), $target->id, $apply);
                $healedSpecific++;
            }
        }

        // 5. Bilinen Özel GSC 404 Tarif Eşleştirmeleri
        $specificRecipeMappings = [
            'sutlac-kibris' => 'sutlac',
            'hellimli-kahvalti-tavas' => 'hellimli-yumurta',
            'meze-tatlilar' => 'kibris-tatlisi',
            'domatesli-tavuk-5GGDA' => 'domatesli-tavuk-sote',
            'lahmacun-durum-8FQ8V' => 'lahmacun',
            'midye-EG6Y4' => 'zeytinyagli-enginar',
            'sardalya-0NAD4' => 'hizli-balik-tava',
            'antalya-usulu-tahinli-piyaz-1XF5W' => 'humus',
            'firinda-baharatli-tavuk' => 'firin-tavuk-baget',
            'tavuklu-bulgur-pilavi' => 'vegan-bulgur-pilavi',
            'atom-acili-ezme-mezesi-3VD3J' => 'humus',
            'domatesli-tavuk-sote' => 'tavuk-sote',
            'icli-kofte-9YDMX' => 'izmir-kofte',
            'limonlu-balik-tepsisi' => 'tavada-levrek',
            'pirincli-tavuk-corbasi' => 'tavuk-suyu-corbasi',
            'yogurtlu-nohut-salatasi' => 'humus',
        ];

        foreach ($specificRecipeMappings as $oldSlug => $targetSlug) {
            $target = Recipe::query()->where('status', 'published')->where('verification_status', 'verified')->where('slug', $targetSlug)->first();
            if ($target === null) {
                $targets = Recipe::query()->where('status', 'published')->where('verification_status', 'verified')
                    ->where('slug', 'like', $targetSlug.'-%')
                    ->limit(2)
                    ->get();
                $target = $targets->count() === 1 ? $targets->first() : null;
            }
            if ($target !== null) {
                $this->registerRedirect(Recipe::class, $oldSlug, $target->id, $apply);
                $this->registerRedirect(Recipe::class, rawurldecode($oldSlug), $target->id, $apply);
                $this->registerRedirect(Recipe::class, rawurlencode($oldSlug), $target->id, $apply);
                $healedRecipes++;
            }
        }

        // 5. Taslak Tarifler -> Yayındaki Eşine Bağlama
        $draftRecipes = Recipe::query()->where('status', 'draft')->get(['id', 'slug', 'title', 'category_id']);
        foreach ($draftRecipes as $draftR) {
            $twinR = Recipe::query()->where('status', 'published')->where('verification_status', 'verified')
                ->where('title', $draftR->title)
                ->where('id', '!=', $draftR->id)
                ->first();

            if ($twinR === null) {
                $rootSlug = (string) preg_replace('/-[A-Za-z0-9]{5}$/', '', (string) $draftR->slug);
                if ($rootSlug !== '' && $rootSlug !== $draftR->slug) {
                    $twinR = Recipe::query()->where('status', 'published')->where('verification_status', 'verified')
                        ->where(fn ($q) => $q->where('slug', $rootSlug)->orWhere('slug', 'like', $rootSlug.'-%'))
                        ->first();
                }
            }

            if ($twinR !== null) {
                $this->registerRedirect(Recipe::class, (string) $draftR->slug, $twinR->id, $apply);
                $this->registerRedirect(Recipe::class, rawurldecode((string) $draftR->slug), $twinR->id, $apply);
                $healedRecipes++;
            }
        }

        $this->components->info(sprintf(
            'Sonuç: %d Geo Blog, %d Taslak Blog, %d Özel Eşleşme, %d Tarif yönlendirmesi hazırlandı.',
            $healedGeo,
            $healedDraftBlogs,
            $healedSpecific,
            $healedRecipes
        ));

        if (! $apply) {
            $this->comment('Değişiklikleri veritabanına yazmak için --apply bayrağını kullanın.');
        }

        return self::SUCCESS;
    }

    /** Bir hedefe bu kadar eski adres bağlanırsa eşleştirme bozulmuş demektir. */
    public const HEDEF_BASINA_AZAMI = 5;

    private function registerRedirect(string $modelType, string $oldSlug, int $modelId, bool $apply): void
    {
        if (! $apply) {
            return;
        }

        // Aynı hedefe yığılan yönlendirme eşleştirmenin bozulduğunun işareti.
        // 22 Eylül 2026'da ölçüldü: 1.467 kaydın 1.357'si dokuz hedefe
        // yığılmıştı; tek bir mercimek çorbası tarifine 999 ayrı adres
        // yönleniyordu. Köz patlıcan sayfası kuru bakliyat yazısına
        // gidiyordu. Yanlış yönlendirme, 404'ten daha zararlı.
        $mevcut = SlugRedirect::query()
            ->where('model_type', $modelType)
            ->where('model_id', $modelId)
            ->where('old_slug', '!=', $oldSlug)
            ->count();

        if ($mevcut >= self::HEDEF_BASINA_AZAMI) {
            $this->components->warn('Atlandı, hedefe zaten '.$mevcut.' adres bağlı: '.$oldSlug);

            return;
        }

        if ($modelType === Recipe::class) {
            $valid = Recipe::query()->where('status', 'published')->where('verification_status', 'verified')->where('id', $modelId)->exists();
        } else {
            $valid = BlogPost::query()->publiclyVisible()->where('id', $modelId)->exists();
        }

        if (! $valid) {
            $this->components->warn('Atlandı, hedef yayınlanmış geçerli bir kayıt değil: '.$oldSlug.' -> '.$modelType.' #'.$modelId);

            return;
        }

        SlugRedirect::query()->updateOrCreate(
            ['model_type' => $modelType, 'old_slug' => $oldSlug],
            ['model_id' => $modelId]
        );
    }
}
