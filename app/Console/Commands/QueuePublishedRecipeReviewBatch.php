<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\PublishedRecipeReviewQueueService;
use Illuminate\Console\Command;

final class QueuePublishedRecipeReviewBatch extends Command
{
    protected $signature = 'content:published-recipe-review-batch {--limit=10 : Bir batch içindeki tarif sayısı} {--page=1 : Sabit sıralamadaki batch sayfası} {--remaining : Tamamlanan ilk sayfayı atlayıp kalan published kataloğu işle} {--all : Seçilen scope içindeki tüm sayfaları ayrı batchler halinde işle} {--scope=published : published veya draft} {--write : Yalnız private inceleme kayıtlarını oluştur} {--json : Makine okunabilir çıktı}';

    protected $description = 'Yayınlanan tarifler için private kimlik, içerik ve medya inceleme kuyruğu hazırlar; yayınlama veya doğrulama yapmaz.';

    public function handle(PublishedRecipeReviewQueueService $queue): int
    {
        $limit = (int) $this->option('limit');
        $page = (int) $this->option('page');
        $scope = (string) $this->option('scope');
        if ($limit < 1 || $limit > 50) {
            $this->error('limit 1 ile 50 arasında olmalıdır.');

            return self::INVALID;
        }
        if ($page < 1 || $page > 50) {
            $this->error('page 1 ile 50 arasında olmalıdır.');

            return self::INVALID;
        }
        if (! in_array($scope, ['published', 'draft'], true)) {
            $this->error('scope published veya draft olmalıdır.');

            return self::INVALID;
        }

        if ($this->option('remaining') && $scope !== 'published') {
            $this->error('remaining yalnız published katalog için kullanılabilir.');

            return self::INVALID;
        }
        if ($this->option('remaining') && $this->option('all')) {
            $this->error('remaining ve all birlikte kullanılamaz.');

            return self::INVALID;
        }

        if ($this->option('remaining')) {
            $result = $this->queuePages($queue, $limit, 2, (bool) $this->option('write'), 'published') + ['completed_batch_one_excluded' => true];
        } elseif ($this->option('all')) {
            $result = $this->queuePages($queue, $limit, 1, (bool) $this->option('write'), $scope);
        } else {
            $result = $queue->queue($limit, $page, (bool) $this->option('write'), $scope);
        }
        $result += [
            'page' => $page,
            'scope' => $scope,
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

    /** @return array<string,mixed> */
    private function queuePages(PublishedRecipeReviewQueueService $queue, int $limit, int $startPage, bool $write, string $scope): array
    {
        $result = ['selected' => 0, 'queued' => 0, 'identity_states' => [], 'media_waiting' => 0, 'recipes' => []];
        $processedBatches = 0;
        for ($page = $startPage; ; $page++) {
            $batch = $queue->queue($limit, $page, $write, $scope);
            if ($batch['selected'] === 0) {
                break;
            }
            $processedBatches++;
            $result['selected'] += $batch['selected'];
            $result['queued'] += $batch['queued'];
            $result['media_waiting'] += $batch['media_waiting'];
            $result['recipes'] = [...$result['recipes'], ...$batch['recipes']];
            foreach ($batch['identity_states'] as $state => $count) {
                $result['identity_states'][$state] = ($result['identity_states'][$state] ?? 0) + $count;
            }
        }
        $result['start_page'] = $startPage;
        $result['processed_batches'] = $processedBatches;

        return $result;
    }
}
