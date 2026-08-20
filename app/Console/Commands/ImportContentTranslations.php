<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Models\Recipe;
use Illuminate\Console\Command;

/**
 * Teslim edilen içerik çevirilerini içeri alır.
 *
 * Yalnız JSON okur. Bu dizine ajanlar yazıyor ve oradan PHP çalıştırmak
 * doğrudan uzaktan kod çalıştırma olurdu; JSON çalıştırılamaz.
 *
 * Kısmi çeviri kabul edilmez: bir kaydın çevrilebilir alanlarının hepsi
 * dolu değilse dosya reddedilir. Başlığı İngilizce, açıklaması Türkçe bir
 * sayfa, çevrilmemiş sayfanın kendisidir ve arama motoru tarafında aynı
 * içeriğin ikinci kopyası olarak görünür.
 *
 * Komut hiçbir koşulda dil etkinleştirmez; o karar insana ait.
 */
final class ImportContentTranslations extends Command
{
    protected $signature = 'content:import-translations
        {--apply : Değişiklikleri yaz. Varsayılan yalnız rapordur.}
        {--locale= : Yalnız tek bir dili işle}';

    protected $description = 'Teslim edilen içerik çevirilerini denetler ve içeri alır.';

    public function handle(): int
    {
        $yaz = (bool) $this->option('apply');
        $yalnizDil = (string) ($this->option('locale') ?? '');
        $kok = storage_path('app/content-import/translations');

        if (! is_dir($kok)) {
            $this->components->info('Teslim klasörü yok: '.$kok);

            return self::SUCCESS;
        }

        $desteklenen = (array) config('locales.supported', []);
        $varsayilan = (string) config('locales.default', 'tr');

        $alinan = 0;
        $atlanan = [];

        foreach (glob($kok.'/*', GLOB_ONLYDIR) ?: [] as $dilDizini) {
            $locale = basename($dilDizini);

            if ($yalnizDil !== '' && $locale !== $yalnizDil) {
                continue;
            }

            if (! array_key_exists($locale, $desteklenen)) {
                $atlanan['desteklenmeyen dil: '.$locale][] = $locale;

                continue;
            }

            if ($locale === $varsayilan) {
                $atlanan['kaynak dil çevrilmez: '.$locale][] = $locale;

                continue;
            }

            // Yalnız .json. Buraya bırakılan çalıştırılabilir dosya okunmaz.
            foreach (glob($dilDizini.'/*.php') ?: [] as $reddedilen) {
                $this->warn('Yok sayıldı (bu klasörde PHP çalıştırılmaz): '.basename($reddedilen));
            }

            foreach (glob($dilDizini.'/*.json') ?: [] as $dosya) {
                $ad = basename($dosya);
                $veri = json_decode((string) file_get_contents($dosya), true);

                if (! is_array($veri)) {
                    $atlanan['okunamayan JSON'][] = $ad;

                    continue;
                }

                $tur = (string) ($veri['type'] ?? '');
                $slug = (string) ($veri['slug'] ?? '');
                $alanlar = is_array($veri['fields'] ?? null) ? $veri['fields'] : [];

                $kayit = match ($tur) {
                    'recipe' => Recipe::query()->where('slug', $slug)->first(),
                    'blog' => BlogPost::query()->where('slug', $slug)->first(),
                    default => null,
                };

                if ($kayit === null) {
                    $atlanan[$tur === '' ? 'tür belirtilmemiş' : 'kayıt bulunamadı ('.$tur.')'][] = $ad;

                    continue;
                }

                $gerekli = $kayit->translatableFields();
                $eksik = array_values(array_filter(
                    $gerekli,
                    static fn (string $alan): bool => ! is_string($alanlar[$alan] ?? null) || trim((string) $alanlar[$alan]) === ''
                ));

                if ($eksik !== []) {
                    $atlanan['eksik alan: '.implode(', ', $eksik)][] = $ad;

                    continue;
                }

                // Türkçe metnin olduğu gibi kopyalanması alanı doluymuş gibi
                // gösterir ama sayfa Türkçe kalır; engellemeye çalıştığımız
                // durumun ta kendisi.
                $aynisi = array_values(array_filter(
                    $gerekli,
                    static fn (string $alan): bool => trim((string) $alanlar[$alan]) === trim((string) $kayit->getAttribute($alan))
                ));

                if ($aynisi !== []) {
                    $atlanan['kaynak metnin kopyası: '.implode(', ', $aynisi)][] = $ad;

                    continue;
                }

                if (! $yaz) {
                    $alinan++;

                    continue;
                }

                foreach ($gerekli as $alan) {
                    $kayit->translations()->updateOrCreate(
                        ['locale' => $locale, 'field' => $alan],
                        ['value' => trim((string) $alanlar[$alan])]
                    );
                }

                $alinan++;
            }
        }

        $this->newLine();
        $this->components->info($alinan.($yaz ? ' kayıt çevirisi içeri alındı.' : ' kayıt içeri alınmaya hazır.'));

        foreach ($atlanan as $sebep => $liste) {
            $this->components->warn(count($liste).' atlandı — '.$sebep);
            foreach (array_slice($liste, 0, 5) as $ad) {
                $this->line('    · '.$ad);
            }
        }

        if (! $yaz) {
            $this->newLine();
            $this->line('Yazmak için: <options=bold>php artisan content:import-translations --apply</>');
        }

        $this->newLine();
        $this->line('Çeviri içeri girmek dili açmaz; dil etkinleştirmesi ayrı ve insana ait bir karardır.');

        return self::SUCCESS;
    }
}
