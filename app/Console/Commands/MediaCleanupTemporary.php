<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

final class MediaCleanupTemporary extends Command
{
    protected $signature = 'media:cleanup-temporary {--hours=24 : Yaş eşiği} {--json : Makine okunabilir çıktı}';

    protected $description = 'Private temporary medya dosyalarını dry-run olarak raporlar';

    public function handle(): int
    {
        $threshold = now()->subHours(max(1, (int) $this->option('hours')))->timestamp;
        $files = collect(Storage::disk('local')->allFiles('temporary'))
            ->filter(fn (string $path): bool => Storage::disk('local')->lastModified($path) < $threshold)
            ->values();
        $result = ['eligible_for_cleanup' => $files->count(), 'paths' => $files->all()];
        if ($this->option('json')) {
            $this->line((string) json_encode($result, JSON_UNESCAPED_SLASHES));
        } else {
            $this->info("{$result['eligible_for_cleanup']} geçici dosya cleanup için uygun. Bu komut dry-run çalışır.");
        }

        return self::SUCCESS;
    }
}
