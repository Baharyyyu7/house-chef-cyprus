<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BlogPost;
use App\Models\ContentFactoryBatch;
use App\Models\ContentFactoryIssue;
use App\Models\Recipe;

/** Calculates operational readiness only; it is never a publication decision. */
final class ContentFactoryBatchReadinessService
{
    public const RULE_VERSION = '2026-07-31.1';

    /** @return array<string, mixed> */
    public function batch(ContentFactoryBatch $batch): array
    {
        $issues = ContentFactoryIssue::query()->where('content_factory_batch_id', $batch->id);
        $open = fn (string $severity) => (clone $issues)->where('risk_level', $severity)->whereNotIn('status', ['approved_correction', 'rejected', 'not_applicable'])->count();
        $critical = $open('critical');
        $high = $open('high');
        $deferredHigh = (clone $issues)->where('risk_level', 'high')->where('status', 'deferred')->count();
        $total = $batch->content_type === 'recipe' ? $batch->recipes()->count() : $batch->blogPosts()->count();
        $highBlocking = max(0, $high - $deferredHigh);
        $reasons = [];
        if ($critical > 0) {
            $reasons[] = 'Açık kritik fact-check konusu var.';
        }
        if ($highBlocking > 0) {
            $reasons[] = 'Çözülmemiş yüksek riskli fact-check konusu var.';
        }
        if ($total === 0) {
            $reasons[] = 'Batch içerik içermiyor.';
        }
        $score = max(0, 100 - ($critical * 20) - ($highBlocking * 5) - ($open('medium') * 2) - $open('low'));
        if ($critical > 0) {
            $score = min($score, 59);
        }
        $ready = $critical === 0 && $highBlocking === 0 && $total > 0;

        return ['batch_public_id' => $batch->public_id, 'content_type' => $batch->content_type, 'total_items' => $total, 'critical_open' => $critical, 'high_open' => $high, 'high_deferred' => $deferredHigh, 'medium_open' => $open('medium'), 'low_open' => $open('low'), 'overall_score' => $score, 'blocking_reasons' => $reasons, 'ready_for_human_review' => $ready, 'rule_version' => self::RULE_VERSION, 'last_scanned_at' => now()->toIso8601String(), 'human_review_pending' => $total];
    }

    /** @return array<string, array{total:int,ready:int,blocked:int}> */
    public function summary(): array
    {
        $result = ['recipe' => ['total' => 0, 'ready' => 0, 'blocked' => 0], 'blog' => ['total' => 0, 'ready' => 0, 'blocked' => 0]];
        ContentFactoryBatch::query()->orderBy('id')->each(function (ContentFactoryBatch $batch) use (&$result): void {
            $readiness = $this->batch($batch);
            $type = $batch->content_type;
            $result[$type]['total']++;
            $result[$type][$readiness['ready_for_human_review'] ? 'ready' : 'blocked']++;
        });

        return $result;
    }

    /** @return array<string, array{total:int,ready:int,blocked:int}> */
    public function scan(): array
    {
        ContentFactoryBatch::query()->orderBy('id')->each(function (ContentFactoryBatch $batch): void {
            $readiness = $this->batch($batch);
            $model = $batch->content_type === 'recipe' ? Recipe::class : BlogPost::class;
            $model::query()->where('content_factory_batch_id', $batch->id)->update(['content_factory_readiness_score' => $readiness['overall_score'], 'content_factory_rule_version' => self::RULE_VERSION, 'content_factory_checked_at' => now(), 'content_factory_metadata' => [...$readiness, 'human_approval_required' => true, 'auto_publish' => false]]);
        });

        return $this->summary();
    }
}
