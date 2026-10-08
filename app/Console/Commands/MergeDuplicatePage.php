<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Models\Recipe;
use App\Models\SlugRedirect;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Aynı yemeği ya da konuyu anlatan iki sayfadan zayıfını güçlüsüne bağlar.
 *
 * Kanonik etiketi yalnız bir öneri; iki sayfa da açık kaldıkça birbirinin
 * sıralamasını yer. Zayıf nüsha taslağa alınır, adresi 301 ile güçlüye
 * gider (RedirectsOldSlugs). İçerik silinmez.
 */
final class MergeDuplicatePage extends Command
{
    protected $signature = 'icerik:ikizi-birlestir {tur : tarif|blog} {zayif : Kapanacak slug} {guclu : Kalacak slug} {--apply : Yaz}';

    protected $description = 'Zayıf ikiz sayfayı taslağa alıp adresini güçlü sayfaya 301 ile yönlendirir.';

    public function handle(): int
    {
        $model = match ($this->argument('tur')) {
            'tarif' => Recipe::class,
            'blog' => BlogPost::class,
            default => null,
        };

        if ($model === null) {
            $this->error('tur tarif ya da blog olmalı.');

            return self::INVALID;
        }

        $zayif = $model::query()->where('slug', $this->argument('zayif'))->first();
        $guclu = $model::query()->where('slug', $this->argument('guclu'))->where('status', 'published')->first();

        if ($zayif === null || $guclu === null || $zayif->is($guclu)) {
            $this->error('İki farklı kayıt bulunamadı ya da güçlü sayfa yayında değil.');

            return self::FAILURE;
        }

        $this->line($zayif->slug.' ('.$zayif->status.') → '.$guclu->slug);

        if (! $this->option('apply')) {
            $this->line('Kuru çalıştırma. Yazmak için --apply.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($zayif, $guclu): void {
            $zayif->forceFill(['status' => 'draft'])->save();
            SlugRedirect::query()->updateOrCreate(
                ['model_type' => $guclu->getMorphClass(), 'old_slug' => $zayif->slug],
                ['model_id' => $guclu->getKey()],
            );
        });

        $this->info('Birleştirildi.');

        return self::SUCCESS;
    }
}
