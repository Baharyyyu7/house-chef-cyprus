<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\PublishedRecipeReviewQueueService;
use Illuminate\Console\Command;

final class QueuePublishedRecipeReviewBatch extends Command
{
    protected $signature = 'content:published-recipe-review-batch {--limit=10 : En fazla 50 yayınlanmış tarif} {--write : Yalnız private inceleme kayıtlarını oluştur} {--json : Makine okunabilir çıktı}';

    protected $description = 'Yayınlanan tarifler için private kimlik, içerik ve medya inceleme kuyruğu hazırlar; yayınlama veya doğrulama yapmaz.';

    public function handle(PublishedRecipeReviewQueueService $queue): int
    {
        $limit = (int) $this->option('limit');
        if ($limit < 1 || $limit > 50) {
            $this->error('limit 1 ile 50 arasında olmalıdır.');

            return self::INVALID;
        }

        $result = $queue->queue($limit, (bool) $this->option('write')) + [
            'auto_publish' => false,
            'auto_verify' => false,
            'human_approval_required' => true,
        ];

        if ($this->option('json')) {
            $this->line((string) json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        } else {
            $this->table(['Seçilen', 'Private kuyruğa alındı', 'Medya kimlik incelemesi bekliyor'], [[
                $result['selected'], $result['queued'], $result['media_waiting'],
            ]]);
            $this->info('Tarif, medya, yayın veya doğrulama durumu değiştirilmedi.');
        }

        return self::SUCCESS;
    }
}
