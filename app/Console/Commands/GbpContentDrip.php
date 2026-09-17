<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\BlogPost;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * GBP Content Drip — Google Business Profile için haftalık içerik taslakları üretir.
 *
 * Çıktı: storage/app/content-import/gbp-posts/{YYYY-MM-DD}/ klasörüne JSON dosyaları
 * Hiçbir içerik otomatik yayınlanmaz. Taslaklar insan onayı bekler.
 */
final class GbpContentDrip extends Command
{
    protected $signature   = 'seo:gbp-content-drip
                                {--week=3 : Haftalık kaç post üretilsin}
                                {--dry-run : Dosya yazmaz, sadece listeler}';

    protected $description = 'Google Business Profile için haftalık içerik taslakları hazırlar.';

    /** @var list<array{theme: string, emoji: string, cta: string, tags: list<string>}> */
    private const POST_THEMES = [
        [
            'theme' => 'seasonal_recipe',
            'emoji' => '🍽️',
            'cta'   => 'Tarifi görmek için linke tıklayın!',
            'tags'  => ['#KıbrısYemekleri', '#MediterraneanFood', '#CyprusCooking'],
        ],
        [
            'theme' => 'private_chef',
            'emoji' => '👨‍🍳',
            'cta'   => 'Villa yemeğiniz için rezervasyon alın!',
            'tags'  => ['#PrivateChefCyprus', '#VillaDining', '#KıbrısŞef'],
        ],
        [
            'theme' => 'cooking_tip',
            'emoji' => '💡',
            'cta'   => 'Daha fazla ipucu için web sitemizi ziyaret edin.',
            'tags'  => ['#PişirmeTüyosu', '#CookingTips', '#MutfakHayatı'],
        ],
        [
            'theme' => 'cultural',
            'emoji' => '🏛️',
            'cta'   => 'Kıbrıs mutfağı rehberimizi okuyun.',
            'tags'  => ['#KıbrısMutfağı', '#CyprusFood', '#HalloumiLovers'],
        ],
        [
            'theme' => 'meze',
            'emoji' => '🫙',
            'cta'   => 'Meze masterclass için yer ayırtın!',
            'tags'  => ['#KıbrısMeze', '#CyprusMeze', '#MediterraneanMeze'],
        ],
        [
            'theme' => 'catering',
            'emoji' => '🍾',
            'cta'   => 'Etkinliğiniz için catering teklifi alın.',
            'tags'  => ['#CateringCyprus', '#KıbrısCatering', '#EventCatering'],
        ],
        [
            'theme' => 'cooking_class',
            'emoji' => '📚',
            'cta'   => 'Yemek kursuna kaydolun!',
            'tags'  => ['#CookingClassCyprus', '#YemekKursu', '#LearnCooking'],
        ],
    ];

    public function handle(): int
    {
        $weekCount = (int) $this->option('week');
        $dryRun    = (bool) $this->option('dry-run');

        $this->info("GBP Content Drip" . ($dryRun ? ' [DRY-RUN]' : '') . " — {$weekCount} post/hafta");

        // Yüksek performanslı tarifleri çek
        $topRecipes = DB::table('recipes')
            ->where('status', 'published')
            ->orderBy('view_count', 'desc')
            ->limit(50)
            ->select(['id', 'slug', 'title', 'description'])
            ->get();

        $posts     = [];
        $startDate = now()->next('Monday');

        for ($i = 0; $i < $weekCount; $i++) {
            $theme   = self::POST_THEMES[$i % count(self::POST_THEMES)];
            $recipe  = $topRecipes->random();
            $postDate = $startDate->copy()->addDays($i * 2); // Haftada 3 (Pt, Çrş, Cm)

            $content = $this->buildPostContent($theme, $recipe);

            $posts[] = [
                'date'    => $postDate->toDateString(),
                'theme'   => $theme['theme'],
                'content' => $content,
                'tags'    => $theme['tags'],
                'recipe'  => $recipe ? ['slug' => $recipe->slug, 'title' => $recipe->title] : null,
                'url'     => $recipe ? url('/tarif/' . $recipe->slug) : url('/'),
                'cta'     => $theme['cta'],
                'status'  => 'draft',
            ];

            $this->line("  📅 {$postDate->toDateString()} — {$theme['emoji']} {$theme['theme']}");
        }

        if (!$dryRun) {
            $dir      = storage_path('app/content-import/gbp-posts/' . now()->toDateString());
            $filePath = $dir . '/posts.json';

            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            file_put_contents($filePath, json_encode($posts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $this->info("\n✓ {$weekCount} GBP taslağı yazıldı: {$filePath}");
        }

        return self::SUCCESS;
    }

    /** @param array{theme: string, emoji: string, cta: string, tags: list<string>} $theme */
    private function buildPostContent(array $theme, mixed $recipe): string
    {
        $recipeName = $recipe->title ?? 'Akdeniz Mutfağı';

        return match ($theme['theme']) {
            'seasonal_recipe' => "{$theme['emoji']} Bu haftanın öne çıkan tarifi: **{$recipeName}**!\n\nKıbrıs'ın doğal malzemeleriyle hazırlanan bu lezzeti evinizde kolayca yapabilirsiniz. Otantik Akdeniz usulü, adım adım tarif web sitemizde. {$theme['cta']}",

            'private_chef'    => "{$theme['emoji']} Tatil villanızda unutulmaz bir akşam yemeği hayal edin!\n\nHouse Chef Cyprus'un deneyimli özel şefleri Girne, İskele, Gazimağusa ve Lefkoşa'daki villanıza gelerek sizin için pişirir. Meze sofrasından kleftiko'ya tam deneyim. {$theme['cta']}",

            'cooking_tip'     => "{$theme['emoji']} Mutfak tüyosu: **Hellim peynirini kızartırken...**\n\nHellim / halloumi'yi tavaya koymadan önce kâğıt havluyla kurulayın. Nem, çıtırlığın en büyük düşmanıdır. Orta-yüksek ısı, yağsız tava, 2 dakika her yüz — mükemmel ızgara. {$theme['cta']}",

            'cultural'        => "{$theme['emoji']} Kıbrıs mutfağı 4 kültürün buluşmasıdır: Rum, Türk, İngiliz ve Arap etkileri yüzyıllar boyunca ada mutfağını şekillendirdi.\n\nKleftiko'dan meze kültürüne, Commandaria şarabından anari peyniri tatlılarına — zengin bir miras. {$theme['cta']}",

            'meze'            => "{$theme['emoji']} Kıbrıs'ta meze sadece yemek değil, bir ritüeldir.\n\nSoğuk mezeler, sıcak ara sıcaklar, deniz mahsulleri, ızgara etler... ve her birinin arasında Zivania veya Commandaria yudumu. Evde otantik meze sofrası kurmak ister misiniz? {$theme['cta']}",

            'catering'        => "{$theme['emoji']} Kıbrıs'ta düğün, nişan veya kurumsal etkinlik mi planlıyorsunuz?\n\nHouse Chef Cyprus catering ekibi 10'dan 500 kişiye kadar otantik Kıbrıs ve Akdeniz menüleriyle hizmetinizde. Souvla, kleftiko, meze büfesi ve tam servis ekibi. {$theme['cta']}",

            'cooking_class'   => "{$theme['emoji']} Kıbrıs tatilini öğrenmeyle birleştirin!\n\nProfesyonel şef eşliğinde hellim peyniri yapımı, kleftiko masterclass veya 8 meze atölyesi. Pişirdiğinizi yiyin, tarifleri eve götürün. Bireysel ve grup kursları mevcuttur. {$theme['cta']}",

            default           => "{$theme['emoji']} House Chef Cyprus — Kıbrıs'ın otantik lezzetleri. {$theme['cta']}",
        };
    }
}
