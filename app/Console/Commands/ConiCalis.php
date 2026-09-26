<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Coni\ClaudeConi;
use Illuminate\Console\Command;

/**
 * Claude Coni'nin günlük döngüsü: ölç, değerlendir, her dilde odak seç,
 * iç bağlantıyla yükselt, deftere yaz. Zamanlayıcı aynı servisi doğrudan
 * çağırıyor; bu komut elle koşmak ve çıktıyı görmek için.
 */
final class ConiCalis extends Command
{
    protected $signature = 'coni:calis {--json}';

    protected $description = 'Claude Coni: her dilde ölç, sonuçlanan değişiklikleri değerlendir, yeni odak sayfası seç.';

    public function handle(ClaudeConi $coni): int
    {
        $rapor = $coni->calis();

        if ($this->option('json')) {
            $this->line((string) json_encode($rapor, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

            return self::SUCCESS;
        }

        if (($rapor['olcum'] ?? '') !== 'var') {
            $this->components->warn('Search Console verisi alınamadı; bugün seçim yapılmadı.');

            return self::SUCCESS;
        }

        foreach ($rapor['degerlendirilen'] as $sonuc) {
            $this->line(sprintf('  sonuç #%d %s %s: %s (tıklama %+d)', $sonuc['id'], $sonuc['locale'], $sonuc['kind'], $sonuc['status'], $sonuc['fark']['clicks']));
        }

        foreach ($rapor['diller'] as $dil => $satir) {
            $this->components->twoColumnDetail(
                sprintf('%s  %d tık / %d gös', strtoupper($dil), $satir['olcum']['clicks'], $satir['olcum']['impressions']),
                $satir['odak']['page'] ?? 'odak yok (uygun aday yok)'
            );
        }

        return self::SUCCESS;
    }
}
