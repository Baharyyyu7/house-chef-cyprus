<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ContentFactoryIssue;
use App\Models\EditorialReviewBatch;
use App\Services\EditorialOperationsService;
use Illuminate\Console\Command;

final class ContentEditorialOperationsBrief extends Command
{
    protected $signature = 'content:editorial-operations-brief {--json : Machine-readable output}';

    protected $description = 'Provide a non-publishing daily editorial operations briefing.';

    public function handle(EditorialOperationsService $operations): int
    {
        $metrics = $operations->metrics();
        $result = [
            'total_fact_check_issues' => ContentFactoryIssue::query()->count(),
            'severity' => ContentFactoryIssue::query()->selectRaw('risk_level, count(*) as total')->groupBy('risk_level')->pluck('total', 'risk_level')->all(),
            'source_required' => $metrics['source_required'],
            'evidence_ready' => $metrics['evidence_ready'],
            'review_batches' => $metrics['batches'],
            'recipe_packs' => $metrics['recipe_packs'],
            'blog_packs' => $metrics['blog_packs'],
            'final_approvals_waiting' => $metrics['final_waiting'],
            'media_review_waiting' => $metrics['media_waiting'],
            'reviewer_workload' => $operations->workload()->map(fn (array $item): array => ['public_id' => $item['user']->public_id, 'assigned_batches' => $item['assigned_batches'], 'open_issues' => $item['open_issues'], 'recipe_packs' => $item['recipe_packs'], 'blog_packs' => $item['blog_packs']])->all(),
            'overdue_review_tasks' => EditorialReviewBatch::query()->whereIn('status', ['planned', 'evidence_preparation', 'in_review'])->where('updated_at', '<', now()->subDays(7))->count(),
            'top_20_priority_tasks' => ContentFactoryIssue::query()->whereNotIn('status', ['approved_correction', 'rejected', 'not_applicable'])->orderByDesc('priority_score')->limit(20)->get(['public_id', 'risk_level', 'risk_domain', 'priority_score', 'claim'])->toArray(),
            'blocked_batches' => EditorialReviewBatch::query()->whereIn('status', ['blocked', 'evidence_preparation'])->count(),
            'scheduler_status' => 'registered',
            'queue_status' => config('queue.default'),
            'last_failed_job' => null,
            'auto_publish' => false,
            'human_approval_required' => true,
            'generated_at' => now()->toIso8601String(),
        ];
        if ($this->option('json')) {
            $this->line((string) json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }
        foreach ($result as $key => $value) {
            $this->line($key.': '.(is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value));
        }

        return self::SUCCESS;
    }
}
