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

                $post = BlogPost::query()->publiclyVisible()->where('title', $title)->first();
                if ($post === null) {
                    $cleanSlug = str_replace('geo-2026-', '', $geoSlug);
                    $post = BlogPost::query()->publiclyVisible()
                        ->where('slug', $cleanSlug)
                        ->orWhere('slug', 'like', $cleanSlug . '-%')
                        ->first();
                }

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
                        ->where('slug', $rootSlug)
                        ->orWhere('slug', 'like', $rootSlug . '-%')
                        ->first();
                }
            }

            if ($twin !== null) {
                $this->registerRedirect(BlogPost::class, (string) $draft->slug, $twin->id, $apply);
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
        ];

        foreach ($specificBlogMappings as $oldSlug => $targetSlug) {
            $target = BlogPost::query()->publiclyVisible()->where('slug', $targetSlug)->first();
            if ($target !== null) {
                $this->registerRedirect(BlogPost::class, $oldSlug, $target->id, $apply);
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
        ];

        foreach ($specificRecipeMappings as $oldSlug => $targetSlug) {
            $target = Recipe::query()->where('status', 'published')->where('verification_status', 'verified')->where('slug', $targetSlug)->first();
            if ($target === null) {
                $target = Recipe::query()->where('status', 'published')->where('verification_status', 'verified')->first();
            }

            if ($target !== null) {
                $this->registerRedirect(Recipe::class, $oldSlug, $target->id, $apply);
                $healedRecipes++;
            }
        }

        // 5. Taslak Tarifler -> Yayındaki Eşine Bağlama
        $draftRecipes = Recipe::query()->where('status', 'draft')->get(['id', 'slug', 'title']);
        foreach ($draftRecipes as $draftR) {
            $twinR = Recipe::query()->where('status', 'published')->where('verification_status', 'verified')
                ->where('title', $draftR->title)
                ->where('id', '!=', $draftR->id)
                ->first();

            if ($twinR !== null) {
                $this->registerRedirect(Recipe::class, (string) $draftR->slug, $twinR->id, $apply);
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

    private function registerRedirect(string $modelType, string $oldSlug, int $modelId, bool $apply): void
    {
        if (! $apply) {
            return;
        }

        SlugRedirect::query()->updateOrCreate(
            ['model_type' => $modelType, 'old_slug' => $oldSlug],
            ['model_id' => $modelId]
        );
    }
}
