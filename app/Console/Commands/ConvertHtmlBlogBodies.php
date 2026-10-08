<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Models\ContentTranslation;
use App\Services\Seo\BlogOriginalityService;
use App\Support\HtmlToMarkdown;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

final class ConvertHtmlBlogBodies extends Command
{
    protected $signature = 'blog:html-govde-cevir {--apply : Değişiklikleri yaz (yoksa yalnız rapor)} {--slug=* : Yalnız bu yazılar}';

    protected $description = 'Ham HTML gövdeli yazıları (sayfada boş basılanları) Markdown\'a çevirir; önce yedek alır.';

    public function handle(HtmlToMarkdown $cevirici, BlogOriginalityService $originality): int
    {
        $slugs = (array) $this->option('slug');
        $hedefler = [];

        BlogPost::query()
            ->when($slugs !== [], fn ($q) => $q->whereIn('slug', $slugs))
            ->orderBy('id')
            ->each(function (BlogPost $yazi) use (&$hedefler, $originality): void {
                $govde = (string) $yazi->getRawOriginal('body');
                if (in_array('ham_html', $originality->govdeKusurlari($govde), true)) {
                    $hedefler[] = ['tur' => 'post', 'id' => $yazi->id, 'slug' => $yazi->slug, 'status' => $yazi->status, 'eski' => $govde];
                }
            });

        ContentTranslation::query()
            ->where('translatable_type', BlogPost::class)
            ->where('field', 'body')
            ->orderBy('id')
            ->each(function (ContentTranslation $ceviri) use (&$hedefler, $originality, $slugs): void {
                if (! in_array('ham_html', $originality->govdeKusurlari((string) $ceviri->value), true)) {
                    return;
                }
                $slug = BlogPost::query()->whereKey($ceviri->translatable_id)->value('slug');
                if ($slugs !== [] && ! in_array($slug, $slugs, true)) {
                    return;
                }
                $hedefler[] = ['tur' => 'translation:'.$ceviri->locale, 'id' => $ceviri->id, 'slug' => $slug, 'status' => null, 'eski' => (string) $ceviri->value];
            });

        $satirlar = [];
        foreach ($hedefler as $i => $hedef) {
            $yeni = $cevirici->convert($hedef['eski']);
            $hedefler[$i]['yeni'] = $yeni;
            $satirlar[] = [$hedef['tur'], $hedef['slug'], $hedef['status'] ?? '-', mb_strlen(strip_tags($hedef['eski'])), mb_strlen($yeni)];
        }

        $this->table(['tür', 'slug', 'durum', 'eski metin', 'yeni markdown'], $satirlar);
        $this->info(count($hedefler).' HTML gövde bulundu.');

        if (! $this->option('apply') || $hedefler === []) {
            $this->line('Kuru çalıştırma. Yazmak için --apply.');

            return self::SUCCESS;
        }

        $yedek = 'yedek/html-govde-'.now()->format('Ymd-His').'.json';
        Storage::disk('local')->put($yedek, (string) json_encode($hedefler, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        $this->line('Yedek: '.Storage::disk('local')->path($yedek));

        foreach ($hedefler as $hedef) {
            if ($hedef['tur'] === 'post') {
                BlogPost::query()->whereKey($hedef['id'])->update(['body' => $hedef['yeni']]);
            } else {
                ContentTranslation::query()->whereKey($hedef['id'])->update(['value' => $hedef['yeni']]);
            }
        }

        $this->info(count($hedefler).' gövde Markdown\'a çevrildi.');

        return self::SUCCESS;
    }
}
