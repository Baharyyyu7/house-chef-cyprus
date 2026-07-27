<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

final class MediaCleanupTemporary extends Command
{
    protected $signature = 'media:cleanup-temporary {--hours=24 : Yaş eşiği} {--delete : Güvenli geçici dosyaları sil} {--json : Makine okunabilir çıktı}';

    protected $description = 'Private temporary medya dosyalarını dry-run olarak raporlar';

    public function handle(): int
    {
        $threshold = now()->subHours(max(1, (int) $this->option('hours')))->timestamp;
        $files = collect(Storage::disk('local')->allFiles('temporary'))
            ->filter(fn (string $path): bool => str_starts_with($path, 'temporary/') && ! str_contains($path, '..') && Storage::disk('local')->lastModified($path) < $threshold)
            ->values();
        $deleted = 0;
        if ((bool) $this->option('delete')) {
            foreach ($files as $path) {
                if (Storage::disk('local')->delete($path)) {
                    $deleted++;
                }
            }
            AuditLog::create(['action' => 'media.temporary_cleanup', 'metadata' => ['deleted' => $deleted]]);
        }
        $result = ['dry_run' => ! (bool) $this->option('delete'), 'eligible_for_cleanup' => $files->count(), 'deleted' => $deleted, 'paths' => $files->all()];
        if ($this->option('json')) {
            $this->line((string) json_encode($result, JSON_UNESCAPED_SLASHES));
        } else {
            $this->info("{$result['eligible_for_cleanup']} geçici dosya cleanup için uygun. Bu komut dry-run çalışır.");
        }

        return self::SUCCESS;
    }
}
