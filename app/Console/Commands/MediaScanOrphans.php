<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

final class MediaScanOrphans extends Command
{
    protected $signature = 'media:scan-orphans {--json : Makine okunabilir çıktı}';

    protected $description = 'Public medya diskindeki veritabanına bağlı olmayan dosyaları dry-run olarak listeler';

    public function handle(): int
    {
        $files = Storage::disk('public')->allFiles('media');
        $known = Media::query()->where('disk', 'public')->whereNotNull('path')->pluck('path')->all();
        $orphans = array_values(array_diff($files, $known));
        $result = ['scanned' => count($files), 'orphans' => count($orphans), 'paths' => $orphans];
        if ($this->option('json')) {
            $this->line((string) json_encode($result, JSON_UNESCAPED_SLASHES));
        } else {
            $this->info("{$result['scanned']} dosya tarandı; {$result['orphans']} yetim dosya bulundu.");
        }

        return self::SUCCESS;
    }
}
