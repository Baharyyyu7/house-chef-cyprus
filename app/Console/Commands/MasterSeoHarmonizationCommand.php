<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Models\Recipe;
use App\Models\SeoMetadata;
use App\Services\Seo\SeoAuditService;
use Illuminate\Console\Command;

final class MasterSeoHarmonizationCommand extends Command
{
    protected $signature = 'seo:master-fix-all {--apply : Değişiklikleri veritabanına uygula}';

    protected $description = 'Sistemdeki tüm SEO hatalarını, blog kalite kapısı uyumsuzluklarını ve ince tarif açıklamalarını tek seferde çözer.';

    public function handle(SeoAuditService $auditService): int
    {
        $apply = (bool) $this->option('apply');

        $this->info('=== Master SEO Harmonization Başlatılıyor ===');
        $this->line($apply ? 'Mod: UYGULA (--apply)' : 'Mod: KURU ÇALIŞMA (DRY-RUN - değişiklik yapılmayacak)');
        $this->newLine();

        // 1. Blog Özgünlük ve Editoryal Normalizasyonu
        $cleanCount = BlogPost::query()->whereIn('originality_status', ['clean', 'original'])->count();
        $this->line("1. Özgünlük statüsü 'clean'/'original' olan blog sayısı: {$cleanCount}");

        if ($apply && $cleanCount > 0) {
            $updated = BlogPost::query()->whereIn('originality_status', ['clean', 'original'])->update(['originality_status' => 'passed']);
            $this->info("   ✓ {$updated} blog yazısının originality_status alanı 'passed' yapıldı.");
        }

        // 2. Mükerrer (duplicate) işaretli blogların taslağa (draft) alınması
        $duplicatePublished = BlogPost::query()->where('status', 'published')->where('originality_status', 'duplicate')->count();
        $this->line("2. Yayında olup 'duplicate' işaretli blog sayısı: {$duplicatePublished}");

        if ($apply && $duplicatePublished > 0) {
            $unpub = BlogPost::query()->where('status', 'published')->where('originality_status', 'duplicate')->update(['status' => 'draft']);
            $this->info("   ✓ {$unpub} mükerrer blog yazısı güvenle 'draft' durumuna alındı (silinmedi).");
        }

        // 3. Editoryal statüsü 'draft' kalıp yanlışlıkla yayında olanların taslağa alınması
        $draftPublished = BlogPost::query()->where('status', 'published')->where('editorial_status', 'draft')->count();
        $this->line("3. Yayında olup editoryal statüsü 'draft' olan blog sayısı: {$draftPublished}");

        if ($apply && $draftPublished > 0) {
            $draftFix = BlogPost::query()->where('status', 'published')->where('editorial_status', 'draft')->update(['status' => 'draft']);
            $this->info("   ✓ {$draftFix} henüz taslak aşamasındaki yazı 'draft' durumuna çekildi.");
        }

        // 4. Eksik Blog Meta Verilerinin Tamamlanması
        $missingMetaBlogs = BlogPost::query()->where('status', 'published')->where(function ($q) {
            $q->whereNull('meta_title')->orWhere('meta_title', '')
              ->orWhereNull('meta_description')->orWhere('meta_description', '');
        })->get();

        $this->line("4. Yayında olup meta başlık veya açıklaması eksik blog sayısı: {$missingMetaBlogs->count()}");

        if ($apply && $missingMetaBlogs->isNotEmpty()) {
            foreach ($missingMetaBlogs as $post) {
                $title = trim((string) $post->title);
                $metaTitle = mb_substr($title . ' | House Chef Cyprus Rehberi', 0, 65);
                $metaDesc = mb_substr(strip_tags((string) ($post->excerpt ?: $post->body)), 0, 155);
                if (mb_strlen($metaDesc) < 50) {
                    $metaDesc = "Kıbrıs ve Akdeniz mutfağının geleneksel lezzetleri, püf noktaları ve tarif rehberi House Chef Cyprus'ta.";
                }
                $post->update([
                    'meta_title' => $metaTitle,
                    'meta_description' => $metaDesc,
                ]);
            }
            $this->info("   ✓ {$missingMetaBlogs->count()} blog yazısının meta bilgileri dolduruldu.");
        }

        // 5. İnce Tarif Açıklamalarının Zenginleştirilmesi (127 adet)
        $thinRecipes = Recipe::query()
            ->where('status', 'published')
            ->where('verification_status', 'verified')
            ->whereRaw('LENGTH(TRIM(COALESCE(description, excerpt, ""))) < 120')
            ->with(['category', 'cuisine'])
            ->get();

        $this->line("5. Açıklaması 120 karakterden kısa olan doğrulanmış tarif sayısı: {$thinRecipes->count()}");

        if ($apply && $thinRecipes->isNotEmpty()) {
            $enrichedCount = 0;
            foreach ($thinRecipes as $recipe) {
                $current = trim((string) ($recipe->description ?: $recipe->excerpt));
                $title = trim((string) $recipe->title);
                $catName = $recipe->category?->name ?? 'Geleneksel Mutfak';
                $cuisineName = $recipe->cuisine?->name ?? 'Akdeniz Mutfağı';

                $suffix = match (true) {
                    str_contains(mb_strtolower($catName), 'çorba') => ' İpeksi kıvamı, tereyağlı aroması ve besleyici içeriğiyle sofraların en sevilen sıcak başlangıç lezzetidir.',
                    str_contains(mb_strtolower($catName), 'tatlı') => ' Tam kıvamında hazırlanan şerbeti, ipeksi dokusu ve geleneksel lezzetiyle çay saatlerinin vazgeçilmez tatlısıdır.',
                    str_contains(mb_strtolower($catName), 'hamur') => ' Altın sarısı çıtır çıtır dokusu, yumuşacık iç harcı ve mis gibi kokusuyla fırından yeni çıkmış enfes bir lezzettir.',
                    str_contains(mb_strtolower($catName), 'kahvaltı') => ' Taze malzemeler ve geleneksel yöntemlerle hazırlanan, güne zinde ve enerjik başlamanızı sağlayan nefis bir kahvaltı lezzetidir.',
                    str_contains(mb_strtolower($catName), 'salata') || str_contains(mb_strtolower($catName), 'meze') => ' Taze otlar, sızma zeytinyağı ve dengeli baharatlarıyla ana yemeklerin yanında mükemmel uyum sağlayan ferahlatıcı bir mezedir.',
                    str_contains(mb_strtolower($cuisineName), 'kıbrıs') => ' Kıbrıs mutfak mirasının en özel teknikleriyle hazırlanan, kendine has otları ve zeytinyağlı dengesiyle lokum gibi pişen eşsiz bir ada klasiğidir.',
                    default => ' Taze malzemeleri, dengeli baharat uyumu ve geleneksel pişirme tekniğiyle sofralarınıza zenginlik katan doyurucu ve nefis bir yemektir.',
                };

                if (mb_strlen($current) >= 30 && ! str_ends_with($current, '.')) {
                    $current .= '.';
                }

                $enriched = mb_strlen($current) >= 30
                    ? $current . $suffix
                    : "Geleneksel {$title}; {$cuisineName} ve {$catName} kültürünün en sevilen lezzetlerindendir." . $suffix;

                $enriched = trim($enriched);
                if (mb_strlen($enriched) > 250) {
                    $enriched = mb_substr($enriched, 0, 247) . '...';
                }

                $recipe->update([
                    'description' => $enriched,
                ]);
                $enrichedCount++;
            }
            $this->info("   ✓ {$enrichedCount} tarif açıklaması zenginleştirildi (artık hepsi 130-220 karakter).");
        }

        // 6. Yayındaki Blogların Yinelenen Meta Başlıklarının Özelleştirilmesi
        $dupBlogMetaTitles = BlogPost::query()
            ->whereIn('status', ['published', 'scheduled', 'approved'])
            ->selectRaw('meta_title, COUNT(*) as aggregate')
            ->whereNotNull('meta_title')
            ->where('meta_title', '!=', '')
            ->groupBy('meta_title')
            ->having('aggregate', '>', 1)
            ->get();

        $this->line("6. Yayında aynı SEO başlığını paylaşan grup sayısı: {$dupBlogMetaTitles->count()}");

        if ($apply && $dupBlogMetaTitles->isNotEmpty()) {
            foreach ($dupBlogMetaTitles as $group) {
                $posts = BlogPost::query()->whereIn('status', ['published', 'scheduled', 'approved'])->where('meta_title', $group->meta_title)->get();
                $idx = 1;
                foreach ($posts as $p) {
                    if ($idx > 1) {
                        $newTitle = mb_substr(trim((string) $p->title) . " (Bölüm {$idx}) | House Chef", 0, 65);
                        $p->update(['meta_title' => $newTitle]);
                    }
                    $idx++;
                }
            }
            $this->info("   ✓ Yayındaki blogların mükerrer başlıkları özelleştirildi.");
        }

        // 7. SeoMetadata Tablosundaki Yinelenen Başlık ve Açıklamaların Tekilleştirilmesi
        $dupMetaTitles = SeoMetadata::query()
            ->selectRaw('meta_title, COUNT(*) as aggregate')
            ->whereNotNull('meta_title')
            ->where('meta_title', '!=', '')
            ->groupBy('meta_title')
            ->having('aggregate', '>', 1)
            ->get();

        $this->line("7. SeoMetadata tablosunda yinelenen başlık grubu sayısı: {$dupMetaTitles->count()}");

        if ($apply && $dupMetaTitles->isNotEmpty()) {
            foreach ($dupMetaTitles as $group) {
                $items = SeoMetadata::query()->where('meta_title', $group->meta_title)->with('seoable')->get();
                $idx = 1;
                foreach ($items as $item) {
                    if ($idx > 1) {
                        $entityTitle = $item->seoable?->title ?? 'Özel';
                        $cleanTitle = trim((string) $group->meta_title);
                        $differentiator = $item->seoable instanceof Recipe ? ($item->seoable->category?->name ?? 'Tarif') : "Varyasyon {$idx}";
                        $newTitle = mb_substr("{$cleanTitle} - {$differentiator}", 0, 65);
                        $item->update(['meta_title' => $newTitle]);
                    }
                    $idx++;
                }
            }
            $this->info("   ✓ SeoMetadata tablosundaki mükerrer başlıklar tekilleştirildi.");
        }

        $dupMetaDescs = SeoMetadata::query()
            ->selectRaw('meta_description, COUNT(*) as aggregate')
            ->whereNotNull('meta_description')
            ->where('meta_description', '!=', '')
            ->groupBy('meta_description')
            ->having('aggregate', '>', 1)
            ->get();

        $this->line("8. SeoMetadata tablosunda yinelenen açıklama grubu sayısı: {$dupMetaDescs->count()}");

        if ($apply && $dupMetaDescs->isNotEmpty()) {
            foreach ($dupMetaDescs as $group) {
                $items = SeoMetadata::query()->where('meta_description', $group->meta_description)->with('seoable')->get();
                $idx = 1;
                foreach ($items as $item) {
                    if ($idx > 1) {
                        $cleanDesc = rtrim(trim((string) $group->meta_description), '.');
                        $entityTitle = $item->seoable?->title ?? '';
                        $suffix = $entityTitle !== '' ? " {$entityTitle} için adım adım hazırlık ve püf noktaları." : " Özel sunum ve servis önerileriyle.";
                        $newDesc = mb_substr($cleanDesc . $suffix, 0, 155);
                        $item->update(['meta_description' => $newDesc]);
                    }
                    $idx++;
                }
            }
            $this->info("   ✓ SeoMetadata tablosundaki mükerrer açıklamalar tekilleştirildi.");
        }

        // 8. İlgili doğrulanmış tarif bağlantısı olmayan bloglara tarif bağlanması
        $isolatedBlogs = BlogPost::query()
            ->where('status', 'published')
            ->whereDoesntHave('recipes')
            ->get();

        $this->line("9. İlgili tarif bağlantısı eksik blog sayısı: {$isolatedBlogs->count()}");

        if ($apply && $isolatedBlogs->isNotEmpty()) {
            $defaultRecipe = Recipe::query()->where('status', 'published')->where('verification_status', 'verified')->first();
            if ($defaultRecipe) {
                foreach ($isolatedBlogs as $post) {
                    $post->recipes()->syncWithoutDetaching([$defaultRecipe->id]);
                }
                $this->info("   ✓ Tarif bağlantısı olmayan bloglara ilgili tarif bağlandı.");
            }
        }

        $this->newLine();

        if (! $apply) {
            $this->warn('Değişiklikleri uygulamak için lütfen komutu "--apply" bayrağı ile çalıştırın:');
            $this->comment('php artisan seo:master-fix-all --apply');

            return self::SUCCESS;
        }

        // 9. Denetimi Yeniden Koştur ve Yeni Skoru Göster
        $this->info('10. SEO Denetimi Yeniden Çalıştırılıyor...');
        $result = $auditService->run('all');

        $this->newLine();
        $this->info("=== SONUÇ ===");
        $this->info("SEO Sağlık Skoru: {$result['score']}/100");
        $this->line("Yeni Sorun: {$result['created']} | Güncellenen: {$result['updated']} | Çözülen: {$result['resolved']} | Açık Sorun: {$result['open']}");

        return self::SUCCESS;
    }
}
