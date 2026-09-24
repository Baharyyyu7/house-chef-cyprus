<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

final class MediaScanOrphans extends Command
{
    protected $signature = 'media:scan-orphans {--delete : Güvenli orphan dosyalarını sil} {--json : Makine okunabilir çıktı}';

    protected $description = 'Public medya diskindeki veritabanına bağlı olmayan dosyaları dry-run olarak listeler';

    public function handle(): int
    {
        $files = Storage::disk('public')->allFiles('media');
        $known = Media::query()->where('disk', 'public')->whereNotNull('path')->pluck('path')->all();
        $kayitlar = Media::query()->where('disk', 'public')->get();
        $thumbnails = $kayitlar->flatMap(fn (Media $media): array => $media->thumbnail_paths ?? [])->all();
        $variants = $kayitlar->flatMap($this->varyantYollari(...))->all();
        $orphans = array_values(array_filter(array_diff($files, array_merge($known, $thumbnails, $variants)), fn (string $path): bool => str_starts_with($path, 'media/') && ! str_contains($path, '..')));
        $deleted = 0;
        if ((bool) $this->option('delete')) {
            foreach ($orphans as $path) {
                if (Storage::disk('public')->delete($path)) {
                    $deleted++;
                }
            }
            AuditLog::create(['action' => 'media.orphan_cleanup', 'metadata' => ['deleted' => $deleted]]);
        }
        $result = ['dry_run' => ! (bool) $this->option('delete'), 'scanned' => count($files), 'orphans' => count($orphans), 'deleted' => $deleted, 'paths' => $orphans];
        if ($this->option('json')) {
            $this->line((string) json_encode($result, JSON_UNESCAPED_SLASHES));
        } else {
            $this->info("{$result['scanned']} dosya tarandı; {$result['orphans']} yetim dosya bulundu.");
        }

        return self::SUCCESS;
    }

    /**
     * Bir kaydın duyarlı boy varyantlarının disk yolları.
     *
     * Sitede servis edilen görsellerin neredeyse tamamı burada duruyor:
     * `path` yalnız kaynak dosyayı gösteriyor, sayfalarda basılan
     * `media/optimized/<ULID>/800.jpg` gibi dosyalar bu listede. Bunlar
     * bilinen dosyalara katılmazsa tarayıcı 6.721 dosyanın 6.557'sini yetim
     * sayıyor ve `--delete` sitedeki her fotoğrafı siliyordu.
     *
     * @return list<string>
     */
    private function varyantYollari(Media $media): array
    {
        $yollar = [];

        foreach ((array) $media->responsive_variants as $varyant) {
            if (! is_array($varyant)) {
                continue;
            }

            foreach (['jpeg', 'webp', 'avif', 'path'] as $bicim) {
                $yol = $varyant[$bicim] ?? null;

                if (is_string($yol) && $yol !== '') {
                    $yollar[] = $yol;
                }
            }
        }

        return $yollar;
    }
}
