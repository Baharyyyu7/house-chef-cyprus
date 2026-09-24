<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Recipe;
use Illuminate\Console\Command;

/**
 * Zorluk değerlerini kanonik biçime çeker.
 *
 * Alan iki dilde birden saklanıyordu ve iki özellik kataloğun ayrık
 * yarılarını görüyordu: herkese açık filtre "Kolay/Orta/Zor" sorguluyor,
 * kişiselleştirme profili "easy/medium/hard". Model artık yazarken
 * normalleştiriyor; bu komut geçmiş kayıtları düzeltiyor.
 */
final class NormalizeRecipeDifficulty extends Command
{
    protected $signature = 'recipes:normalize-difficulty {--apply : Değişiklikleri yaz} {--json : Makine okunabilir çıktı}';

    protected $description = 'Tariflerin zorluk değerlerini kanonik biçime çeker; varsayılan dry-run.';

    public function handle(): int
    {
        $uygula = (bool) $this->option('apply');
        $degisen = 0;
        $taninmayan = [];

        Recipe::query()->whereNotNull('difficulty')->chunkById(200, function ($tarifler) use ($uygula, &$degisen, &$taninmayan): void {
            foreach ($tarifler as $tarif) {
                $mevcut = (string) $tarif->getRawOriginal('difficulty');
                $kanonik = Recipe::zorlukKanonik($mevcut);

                if ($kanonik === null) {
                    $taninmayan[$mevcut] = ($taninmayan[$mevcut] ?? 0) + 1;

                    continue;
                }

                if ($kanonik === $mevcut) {
                    continue;
                }

                $degisen++;

                if ($uygula) {
                    // Tek alan güncelleniyor; başka hiçbir şeye dokunulmuyor.
                    $tarif->forceFill(['difficulty' => $kanonik])->saveQuietly();
                }
            }
        });

        $sonuc = ['changed' => $degisen, 'applied' => $uygula, 'unrecognised' => $taninmayan];

        $this->line($this->option('json')
            ? (string) json_encode($sonuc, JSON_UNESCAPED_UNICODE)
            : ($uygula ? "Normalleştirilen: {$degisen}" : "Normalleştirilecek: {$degisen} (dry-run)"));

        return self::SUCCESS;
    }
}
