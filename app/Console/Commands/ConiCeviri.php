<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Coni\ClaudeConi;
use Illuminate\Console\Command;
use InvalidArgumentException;
use JsonException;

/**
 * Bir tarifin tek dildeki kalıp çevirisini gerçeğiyle değiştirir.
 *
 * Dosya biçimi (JSON):
 *   {"title": "...", "excerpt": "...", "description": "...", "steps": ["1. adım", "2. adım", ...]}
 *
 * Adım sayısı tarifin adım sayısına eşit olmalı ve sırası Türkçe asılla
 * aynı olmalı. Metni Claude oturumu Türkçe asıldan o dilde yazar; komut
 * yalnız uygular, eskisini saklar, deftere yazar.
 */
final class ConiCeviri extends Command
{
    protected $signature = 'coni:ceviri {slug} {dil} {dosya : JSON dosyası} {--neden=} {--json}';

    protected $description = 'Claude Coni: tarifin yabancı dildeki kalıp çevirisini gerçeğiyle değiştirir, eskisini saklar.';

    public function handle(ClaudeConi $coni): int
    {
        $dosya = (string) $this->argument('dosya');

        if (! is_file($dosya)) {
            $this->components->error('Dosya yok: '.$dosya);

            return self::FAILURE;
        }

        try {
            $ceviri = json_decode((string) file_get_contents($dosya), true, 16, JSON_THROW_ON_ERROR);
            $sonuc = $coni->ceviri((string) $this->argument('slug'), (string) $this->argument('dil'), (array) $ceviri, (string) $this->option('neden'));
        } catch (JsonException|InvalidArgumentException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        if ($this->option('json')) {
            $this->line((string) json_encode($sonuc, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $this->components->info(sprintf('#%d %s: engel %s → %s', $sonuc['id'], $sonuc['page'], $sonuc['engel_once'] ?? 'yok', $sonuc['engel_sonra'] ?? 'yok (dizine açık)'));

        return self::SUCCESS;
    }
}
