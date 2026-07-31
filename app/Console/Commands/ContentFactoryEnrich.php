<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ContentFactoryEnrichmentService;
use Illuminate\Console\Command;

final class ContentFactoryEnrich extends Command
{
    protected $signature = 'content:factory-enrich {--type=all : recipe, blog veya all} {--batch= : Yalnız bir batch sırası} {--json}';

    protected $description = 'Taslak içerikleri yayınlamadan editoryal derinlik, QA, SEO ve fact-check kuyruklarıyla zenginleştirir.';

    public function handle(ContentFactoryEnrichmentService $enrichment): int
    {
        if (! config('content_factory.enabled')) {
            $this->error('İçerik otomasyonu kapalı.');

            return self::FAILURE;
        }
        $type = (string) $this->option('type');
        $batch = $this->option('batch') === null ? null : (int) $this->option('batch');
        if ($batch !== null && ($batch < 1 || $batch > 18)) {
            $this->error('Batch 1-18 aralığında olmalıdır.');

            return self::INVALID;
        }
        $result = match ($type) {
            'recipe' => ['recipes' => $enrichment->enrichRecipes($batch)],
            'blog' => ['blogs' => $enrichment->enrichBlogs($batch)],
            'all' => $batch === null ? $enrichment->enrichAll() : ['recipes' => $enrichment->enrichRecipes($batch), 'blogs' => $enrichment->enrichBlogs($batch)],
            default => null,
        };
        if ($result === null) {
            $this->error('Geçersiz tür: recipe, blog veya all kullanın.');

            return self::INVALID;
        }
        $payload = array_merge($result, $enrichment->audit(), ['auto_publish' => false, 'human_approval_required' => true]);
        $this->option('json') ? $this->line((string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) : $this->info('Taslak zenginleştirme tamamlandı; hiçbir içerik yayınlanmadı.');

        return self::SUCCESS;
    }
}
