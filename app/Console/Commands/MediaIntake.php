<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Recipe;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

/**
 * Teslim edilen görselleri tek komutla onay kuyruğuna alır.
 *
 * Elle çalıştırılan zincir üç komuttu ve ortadaki adım şöyle davranıyordu:
 * tek bir kayıt reddedilirse bütün parti duruyordu. Antigravity yayında
 * olmayan bir tarif için görsel ürettiğinde - ki üretiyor - o partideki
 * kusursuz görseller de içeri girmiyordu.
 *
 * Burada her kayıt tek tek denenir. Reddedilen atlanır ve sebebiyle
 * raporlanır; kalanlar içeri girer. Hiçbir görsel yayına çıkmaz: hepsi
 * "onay bekliyor" durumunda durur ve insan onayını bekler.
 */
final class MediaIntake extends Command
{
    protected $signature = 'media:intake
        {--apply : Değişiklikleri yaz. Varsayılan yalnız rapordur.}';

    protected $description = 'Teslim edilen tarif görsellerini denetler, içeri alır ve onay kuyruğuna koyar.';

    public function handle(): int
    {
        $yaz = (bool) $this->option('apply');

        $this->components->info($yaz ? 'Görseller içeri alınıyor.' : 'Rapor modu: hiçbir şey yazılmayacak.');

        // 1. Teslim klasörünü manifeste taşı.
        $this->components->task('Teslim klasörü denetleniyor', function () use ($yaz): bool {
            Artisan::call('media:stage-delivered'.($yaz ? ' --apply' : ''), [], $this->getOutput());

            return true;
        });

        if (! $yaz) {
            $this->newLine();
            $this->line('Yazmak için: <options=bold>php artisan media:intake --apply</>');

            return self::SUCCESS;
        }

        // 2. Kayıtları tek tek içeri al. Toplu çalıştırmada bir başarısızlık
        //    bütün partiyi durduruyordu.
        // Manifest iki yerde olabilir: eski kurulumda storage/app altında,
        // Laravel'in güncel 'local' diskinde storage/app/private altında.
        // İkisi de aranır, yoksa bu bir hata değil - teslim edilmiş dosya yok.
        $adaylar = [
            storage_path('app/media-import/approved-media-manifest.json'),
            storage_path('app/private/media-import/approved-media-manifest.json'),
        ];

        $manifest = null;

        foreach ($adaylar as $aday) {
            if (is_file($aday)) {
                $manifest = $aday;
                break;
            }
        }

        if ($manifest === null) {
            $this->components->info('Manifest yok: içeri alınacak görsel bulunmuyor.');
            $this->newLine();
            $this->line('Onay ekranı: <options=bold>/yonetim/medya?durum=onay</>');
            $this->line('Hiçbir görsel onaysız yayına çıkmaz.');

            return self::SUCCESS;
        }

        $kayitlar = json_decode((string) file_get_contents($manifest), true);
        $kayitlar = is_array($kayitlar) ? ($kayitlar['recipes'] ?? $kayitlar) : [];

        $alinan = 0;
        $atlanan = [];

        foreach ($kayitlar as $kayit) {
            $publicId = is_array($kayit) ? ($kayit['public_id'] ?? null) : null;

            if (! is_string($publicId) || $publicId === '') {
                continue;
            }

            $tarif = Recipe::query()->where('public_id', $publicId)->first();

            // Yayında olmayan bir tarifin görselini almanın anlamı yok: kimse
            // göremez. Sebebi yazıyoruz ki tarif yayına alınınca tekrar
            // denenebilsin.
            if (! $tarif) {
                $atlanan['tarif kaydı yok'][] = $publicId;
                continue;
            }

            if ($tarif->status !== 'published' || $tarif->verification_status !== 'verified') {
                $atlanan['tarif yayında değil ('.$tarif->status.')'][] = $tarif->title;
                continue;
            }

            $sonuc = Artisan::call('media:import-approved', [
                '--content-type' => 'recipe',
                '--public-id' => $publicId,
                '--strict' => true,
                '--replace-fallbacks' => true,
            ]);

            if ($sonuc === 0) {
                $alinan++;
            } else {
                $atlanan['içe aktarma reddetti'][] = $tarif->title;
            }
        }

        // 3. Varyantları üret ve fotoğraf denetimini çalıştır.
        if ($alinan > 0) {
            $this->components->task('Responsive varyantlar üretiliyor', function (): bool {
                Artisan::call('media:optimize');

                return true;
            });
        }

        $this->newLine();
        $this->components->info($alinan.' görsel onay kuyruğuna alındı.');

        foreach ($atlanan as $sebep => $liste) {
            $this->components->warn(count($liste).' atlandı — '.$sebep);
            foreach (array_slice($liste, 0, 5) as $ad) {
                $this->line('    · '.$ad);
            }
            if (count($liste) > 5) {
                $this->line('    · … '.(count($liste) - 5).' tane daha');
            }
        }

        $this->newLine();
        $this->line('Onay ekranı: <options=bold>/yonetim/medya?durum=onay</>');
        $this->line('Hiçbir görsel onaysız yayına çıkmaz.');

        return self::SUCCESS;
    }
}
