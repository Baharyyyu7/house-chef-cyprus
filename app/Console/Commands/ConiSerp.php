<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Coni\ClaudeConi;
use Illuminate\Console\Command;
use InvalidArgumentException;

/**
 * Arama sonucunda görünen başlık/açıklamayı tek dilde değiştirir ve
 * deftere yazar. Metni kalıp değil insan ya da Claude oturumu, o dilin
 * kendisinde yazar; komut yalnız uygular, eskisini saklar ve ölçüye bağlar.
 */
final class ConiSerp extends Command
{
    protected $signature = 'coni:serp {adres : Sayfanın tam adresi} {--baslik= : Marka eki olmadan 15-65 karakter} {--aciklama= : 70-160 karakter} {--neden= : Bu değişiklik neden yapıldı} {--zorla : Ölçülmeyi bekleyen değişikliğin üstüne yaz} {--json}';

    protected $description = 'Claude Coni: tek sayfanın tek dildeki arama sonucu başlığını/açıklamasını değiştirir, eskisini saklar.';

    public function handle(ClaudeConi $coni): int
    {
        try {
            $sonuc = $coni->serp(
                (string) $this->argument('adres'),
                $this->option('baslik') !== null ? (string) $this->option('baslik') : null,
                $this->option('aciklama') !== null ? (string) $this->option('aciklama') : null,
                (string) $this->option('neden'),
                (bool) $this->option('zorla')
            );
        } catch (InvalidArgumentException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        if ($this->option('json')) {
            $this->line((string) json_encode($sonuc, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $this->components->info(sprintf('#%d %s %s', $sonuc['id'], strtoupper($sonuc['dil']), $sonuc['page']));
        $this->line('  önce: '.($sonuc['onceki']['title'] ?? '(yok)').' | '.($sonuc['onceki']['description'] ?? '(yok)'));
        $this->line('  sonra: '.($sonuc['yeni']['title'] ?? '(yok)').' | '.($sonuc['yeni']['description'] ?? '(yok)'));
        $this->line('  geri almak için: php artisan coni:geri-al '.$sonuc['id']);

        return self::SUCCESS;
    }
}
