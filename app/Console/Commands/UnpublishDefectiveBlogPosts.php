<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Services\Seo\BlogOriginalityService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Yayındaki kusurlu yazıları taslağa alır; silmez.
 *
 * 8 Ekim 2026'da indekste aynı cümleyi 142 kez tekrarlayan 25 şablon yazı
 * ve gerçek kişilerin "en sevdiği yemekler" üzerine kaynaksız 16 yazı vardı.
 * İndeks dışı bırakmak yetmez: sayfa açık kaldıkça AdSense incelemesi onu
 * sitenin parçası sayar.
 */
final class UnpublishDefectiveBlogPosts extends Command
{
    protected $signature = 'blog:kusurlulari-yayindan-al
        {--kusurlu : Yazı içi cümle tekrarı ya da yer tutucu taşıyan yazılar}
        {--slug=* : Ayrıca bu yazılar}
        {--apply : Yaz (yoksa yalnız rapor)}';

    protected $description = 'Kusurlu ya da adı verilen yayındaki yazıları yedekleyip taslağa alır.';

    public function handle(BlogOriginalityService $originality): int
    {
        $slugs = array_values(array_filter((array) $this->option('slug')));
        $hedefler = collect();

        if ($this->option('kusurlu')) {
            BlogPost::query()->where('status', 'published')->orderBy('id')->each(function (BlogPost $yazi) use ($originality, $hedefler): void {
                $kusurlar = array_values(array_intersect($originality->govdeKusurlari((string) $yazi->getRawOriginal('body')), ['cumle_tekrari', 'yer_tutucu']));
                if ($kusurlar !== []) {
                    $hedefler->put($yazi->id, ['yazi' => $yazi, 'sebep' => implode(',', $kusurlar)]);
                }
            });
        }

        if ($slugs !== []) {
            BlogPost::query()->where('status', 'published')->whereIn('slug', $slugs)->each(function (BlogPost $yazi) use ($hedefler): void {
                $hedefler->put($yazi->id, ['yazi' => $yazi, 'sebep' => $hedefler->get($yazi->id)['sebep'] ?? 'adla']);
            });
        }

        $this->table(['slug', 'sebep'], $hedefler->map(fn (array $h): array => [$h['yazi']->slug, $h['sebep']])->values()->all());
        $this->info($hedefler->count().' yayındaki yazı seçildi.');

        if (! $this->option('apply') || $hedefler->isEmpty()) {
            $this->line('Kuru çalıştırma. Yazmak için --apply.');

            return self::SUCCESS;
        }

        $yedek = 'yedek/yayindan-alinan-'.now()->format('Ymd-His').'.json';
        Storage::disk('local')->put($yedek, (string) json_encode(
            $hedefler->map(fn (array $h): array => ['sebep' => $h['sebep']] + $h['yazi']->getAttributes())->values()->all(),
            JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
        ));
        $this->line('Yedek: '.Storage::disk('local')->path($yedek));

        BlogPost::query()->whereIn('id', $hedefler->keys()->all())->update(['status' => 'draft']);

        $this->info($hedefler->count().' yazı taslağa alındı.');

        return self::SUCCESS;
    }
}
