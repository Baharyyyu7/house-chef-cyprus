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
            'kibris-usulu-kuru-bakla-favasi-ve-kirmizi-sogan-mezesi-EGCGT' => 'kibris-cakistes-kirma-yesil-zeytin-nasil-yapilir-tatlandirma-rehberi',
            'glutensiz-ekmek-ve-hamur-isi-puf-noktalari-kabarma-ve-doku-sirlari' => 'glutensiz-ekmek-ve-hamur-isi-yapmanin-puf-noktalari-kabarmayan-ve-dagilan-hamurlara-kesin-cozum-EA5T8',
            'kemik-suyu-ve-dogal-kolajen-rehberi-jel-kivaminda-ilikli-kemik-suyu' => 'kemik-suyu-ve-dogal-kolajen-rehberi-jel-kivaminda-ilikli-kemik-suyu-X6HFA',
            'orijinal-lokanta-usulu-mercimek-corbasi-tarifi-ipeksi-kivamin-sirri-KZM3Q' => 'orijinal-lokanta-usulu-mercimek-corbasi-tarifi-ipeksi-kivamin-sirri-3Y13B',
            'akdeniz-levregi-ve-cipura-bugulama-sulu-ve-aromatik-balik-H08HA' => 'firinda-levrek-ve-cipura-bugulama-sebze-yataginda-kurumayan-sulu-balik-pisirme-sanati-SCCFX',
            'arpacik-soganli-kibris-usulu-agir-ateste-KVC3V' => 'kibris-yahni-tarifi-arpacik-soganli-ve-kemikli-etle-agir-ateste-lezzet-patlamasi-X4HDS',
            'asma-yapragina-sarili-sardalya-izgara-dumansiz-lezzet-14R74' => 'asma-yapraginda-sardalya-izgara-dumansiz-ve-kokusuz-balik-pisirme-sanati-M7N3A',
            'besparmak-daglarinda-defne-ve-mersin-yapragi-kullanimi-BRK3A' => 'besparmak-daglarinda-defne-ve-mersin-yapragi-kullanimi-geleneksel-aromalar-J3EBF',
            'citir-sigara-boregi-yag-cekmeyen-kizartma-yontemi-ZAKTG' => 'citir-sigara-boregi-yapmanin-puf-noktalari-yag-cekmeyen-kizartma-teknigi-13N5A',
            'dipkarpaz-dogal-deniz-tuzu-ve-mutfakta-tuzlama-teknikleri-VHDTB' => 'dipkarpaz-dogal-deniz-tuzu-ve-mutfakta-tuzlama-teknikleri-kaya-tuzu-ile-farki-H568D',
            'dogal-yabani-semizotu-ve-suzme-koy-yogurdu-dengesi-W35X3' => 'dogal-yabani-semizotu-ve-suzme-koy-yogurdu-dengesi-ferahlatan-akdeniz-mezesi-M7ZDF',
            'et-pisirme-sicakliklari-rare-medium-ve-well-done-bilimi' => 'et-pisirme-sicakliklari-rare-medium-well-done-ve-bilimsel-dinlendirme-rehberi-DF7HN',
            'frenk-kimyonu-ve-kibris-koy-sucugunun-baharat-dengesi-Q95T1' => 'frenk-kimyonu-ve-kibris-koy-sucugunun-baharat-dengesi-karafaki-aromasi-K9DFX',
            'geleneksel-kibris-paluzesi-uzum-suyu-ve-DWH34' => 'geleneksel-kibris-paluzesi-uzum-suyu-ve-nisasta-ile-tatli-yapimi-M5F2K',
            'guvecte-tereyagli-karides-sarimsak-ve-aci-1S77P' => 'guvecte-tereyagli-karides-sarimsak-ve-aci-biberli-guvec-sosunun-sirri-R5ZFA',
            'kıbrıs-meze-rehberi' => 'kibris-meze-sofrasi-kulturu-ve-paylasma-adabi-tarihsel-bir-lezzet-yolculugu-1F8ND',
            'kibris-flaouna-pilavuna-ozel-peynirli-baharatli-corek-BS9JP' => 'kibris-flaouna-pilavuna-paskalya-coregi-yapimi-ve-mahlep-sakiz-dengesi-J59DZ',
            'kibris-koy-hellimi-yapimi-ve-geleneksel-salamura-sirlari-Y27SB' => 'kibris-koy-hellimi-yapimi-ve-geleneksel-salamura-saklama-sirlari-K34MN',
            'kibris-meze-kulturu-paylasmanin-lezzeti' => 'kibris-meze-sofrasi-kulturu-ve-paylasma-adabi-tarihsel-bir-lezzet-yolculugu-1F8ND',
            'kibris-ta-keci-ve-koyun-yetistiriciligi-ile-mutfak-baglantisi' => 'kibris-ta-keci-ve-koyun-yetistiriciligi-ile-mutfak-kulturu-baglantisi-G7NDS',
            'kirmizi-mercan-ve-barbun-baligi-citir-kizartma-sirlari-3WSX9' => 'kirmizi-mercan-ve-barbun-baligi-citir-kizartma-sirlari-akdeniz-tava-sanati-Q57MN',
            'kis-sofralari-icin-portakalli-zeytinyagli-pirasa-N03AB' => 'kis-sofralari-icin-portakalli-zeytinyagli-pirasa-tarifi-seker-ilavesiz-tatlilik-V78DK',
            'koy-usulu-ebegumeci-ve-zeytinyagli-pazi-sarmasi-sirlari-MMDF4' => 'koy-usulu-ebegumeci-ve-zeytinyagli-pazi-sarmasi-sirlari-ince-sarim-ve-pisirme-L5ZDF',
            'koy-usulu-ilikli-kemik-suyu-12-saat-agir-kaynatma-QYWQP' => 'kemik-suyu-ve-dogal-kolajen-rehberi-jel-kivaminda-ilikli-kemik-suyu-X6HFA',
            'koz-patlicanli-meze-cesitleri-babaganus-ve-mutabbal-TY91J' => 'patlican-salatasi-babaganus',
            'lefke-hurma-agaclari-ve-geleneksel-hurmali-kibris-tatlilari-RC70R' => 'lefke-hurma-agaclari-ve-geleneksel-hurmali-kibris-tatlilari-lokal-lezzet-M9FDK',
            'serdarli-ve-gecitkale-koylerinde-firinda-hellimli-WS987' => 'serdarli-ve-gecitkale-koylerinde-firinda-hellimli-bitta-corek-gelenegi-N8F2Z',
            'tahini-susam-ezmesinin-uretiminden-sofraya' => 'tahini-kibris-susam-ezmesinin-tas-degirmenden-sofraya-yolculugu-K2FDS',
            'tavuk-tantuni-yaparken-etin-suyunu-koruma-yontemleri-8KENJ' => 'tavuk-tantuni-yaparken-etin-suyunu-koruma-yontemleri-pamuk-gibi-lokum-eti-T47MK',
            'yabani-citlembik-murt-taneli-kibris-koy-coregi-QE83K' => 'yabani-citlembik-murt-taneli-kibris-koy-coregi-pisirme-sanati-B98DZ',
            'zeytinyaginda-baharat-ve-sarimsak-dinlendirme-sanati-AXGJK' => 'zeytinyaginda-baharat-ve-sarimsak-dinlendirme-sanati-aromali-yag-rehberi-P37MN',
            'zeytinyagli-yemeklerde-asit-seker-ve-doku-dengesi-BB1SH' => 'zeytinyagli-yemeklerde-asit-seker-ve-doku-dengesi-bilimsel-pisirme-sirlari-G5ZDS',
            'geo-2026-cakistes-kirma-yesil-zeytin-hazirlama-puf-noktalari' => 'kibris-cakistes-kirma-yesil-zeytin-nasil-yapilir-tatlandirma-rehberi',
            'geo-2026-besparmak-daglarinda-defne-ve-mersin-yapragı' => 'besparmak-daglarinda-defne-ve-mersin-yapragi-kullanimi-geleneksel-aromalar-J3EBF',
            'geo-2026-dag-zahteri-ve-sumak-karisimi-kahvalti' => 'dag-zahteri-ve-sumak-karisimi-kahvaltilik-zahter-hazirlama-kultur-Z45DF',
            'geo-2026-dipkarpaz-tuz-golu-ve-dogal-akdeniz-tuzu' => 'dipkarpaz-dogal-deniz-tuzu-ve-mutfakta-tuzlama-teknikleri-kaya-tuzu-ile-farki-H568D',
            'geo-2026-ebegumeci-ve-pazi-sarmasi-koy-usulu' => 'koy-usulu-ebegumeci-ve-zeytinyagli-pazi-sarmasi-sirlari-ince-sarim-ve-pisirme-L5ZDF',
            'geo-2026-frenk-kimyonu-ve-koy-sucugu-baharat-dengesi' => 'frenk-kimyonu-ve-kibris-koy-sucugunun-baharat-dengesi-karafaki-aromasi-K9DFX',
            'geo-2026-kibris-fava-bakla-ezmesi-karamelize-sogan' => 'kibris-fava-ve-bakla-ezmesi-karamelize-sogan-esliginde-ipeksi-lezzet-V67MN',
            'geo-2026-kibris-kaparisi-gabbar-tursusu-ve-kullanimi' => 'kibris-kaparisi-gabbar-tursusu-ve-salatalarda-kullanimi-keskin-tat-M45DZ',
            'geo-2026-kibris-kleftiko-firin-kebabi-pisirme-rehberi' => 'kibris-usulu-kleftiko',
            'geo-2026-kibris-molehiya-tavuklu-ve-kuzu-etli-pisirme' => 'kibris-usulu-molehiya-tarifi',
            'geo-2026-kibris-paluzes-uzum-tatlisi-ve-badem-uyumu' => 'geleneksel-kibris-paluzesi-uzum-suyu-ve-nisasta-ile-tatli-yapimi-M5F2K',
            'geo-2026-kibris-tatlisi-serbetli-ve-cevizli-kek-krema' => 'kibris-tatlisi',
            'geo-2026-lefke-hurma-agaclari-ve-hurmali-tatlilar' => 'lefke-hurma-agaclari-ve-geleneksel-hurmali-kibris-tatlilari-lokal-lezzet-M9FDK',
            'geo-2026-maroni-ve-mesarya-koy-ekmegi-pisirme' => 'maroni-ve-mesarya-koy-ekmegi-eksi-mayali-tas-firin-lezzeti-F89NM',
            'geo-2026-serdarli-ve-gecitkale-hellimli-bitta-firin' => 'serdarli-ve-gecitkale-koylerinde-firinda-hellimli-bitta-corek-gelenegi-N8F2Z',
            'geo-2026-tahinli-patlican-salatasi-mutabbal-ve-babaganus' => 'patlican-salatasi-babaganus',
            'geo-2026-zeytinyagli-enginar-kalbi-ve-bakla-pilaki' => 'zeytinyagli-enginar',
            'geo-2026-zeytinyagli-barbunya-pilaki-ipeksi-sos-sirri' => 'karpaz-yarimadasi-dag-kekigi-ve-yabani-akdeniz-otlari-rehberi-GVM3V',
            'geo-2026-firin-tavuk-ve-baharatli-koy-patatesi' => 'firin-tavuk-baget',
            'geo-2026-sele-zeytini-ve-siyah-zeytin-olgunlastirma' => 'kibris-cakistes-kirma-yesil-zeytin-nasil-yapilir-tatlandirma-rehberi',
            'geo-2026-uzum-sirasi-kofter-ve-sucuk-yapimi-gelenegi' => 'kibris-macunu-cevizli-sucuk',
            'ev-yapimi-peynir-hellim-lor-ve-tulum' => 'kibris-koy-hellimi-yapimi-ve-geleneksel-salamura-saklama-sirlari-K34MN',
            'geleneksel-kibris-corbalarinin-tarihi' => 'klasik-kirmizi-mercimek-corbasi',
            'kibris-gecis-donemi-yemekleri-ramazan-paskalya-ve-noel-sofralari' => 'kibris-meze-sofrasi-kulturu-ve-paylasma-adabi-tarihsel-bir-lezzet-yolculugu-1F8ND',
            'kibris-mutfaginda-hellim-tarihce-uretim-ve-farkli-pisirme-yontemleri' => 'kibris-koy-hellimi-yapimi-ve-geleneksel-salamura-saklama-sirlari-K34MN',
            'kibris-ta-narenciye-limon-portakal-ve-greyfurt-kullanimi' => 'kibris-meze-sofrasi-kulturu-ve-paylasma-adabi-tarihsel-bir-lezzet-yolculugu-1F8ND',
            'kuzu-tandir-etini-lokum-gibi-yapan-dusuk-isi-kontrolu-BXVXS' => 'kibris-usulu-kleftiko',
            'kis-sofrasinin-vazgecilmezi-kuru-baklagiller-ve-hazirlik-yontemleri' => 'klasik-kuru-fasulye',
        ];

        foreach ($specificBlogMappings as $oldSlug => $targetSlug) {
            $targets = BlogPost::query()->publiclyVisible()
                ->where(fn ($query) => $query->where('slug', $targetSlug)->orWhere('slug', 'like', $targetSlug.'-%'))
                ->limit(2)
                ->get();
            $target = $targets->count() === 1 ? $targets->first() : null;
            if ($target !== null) {
                $this->registerRedirect(BlogPost::class, $oldSlug, $target->id, $apply);
                $this->registerRedirect(BlogPost::class, rawurldecode($oldSlug), $target->id, $apply);
                $this->registerRedirect(BlogPost::class, rawurlencode($oldSlug), $target->id, $apply);
                $healedSpecific++;
            }
        }

        // 4. Bilinen Özel GSC 404 Tarif Eşleştirmeleri
        $specificRecipeMappings = [
            'sutlac-kibris' => 'sutlac',
            'hellimli-kahvalti-tavas' => 'hellimli-yumurta',
            'meze-tatlilar' => 'kibris-tatlisi',
            'domatesli-tavuk-5GGDA' => 'domatesli-tavuk-sote',
            'lahmacun-durum-8FQ8V' => 'klasik-lahmacun',
            'midye-EG6Y4' => 'zeytinyagli-enginar',
            'sardalya-0NAD4' => 'firinda-somon',
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
            $targets = Recipe::query()->where('status', 'published')->where('verification_status', 'verified')
                ->where(fn ($query) => $query->where('slug', $targetSlug)->orWhere('slug', 'like', $targetSlug.'-%'))
                ->limit(2)
                ->get();
            $target = $targets->count() === 1 ? $targets->first() : null;
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

        SlugRedirect::query()->updateOrCreate(
            ['model_type' => $modelType, 'old_slug' => $oldSlug],
            ['model_id' => $modelId]
        );
    }
}
