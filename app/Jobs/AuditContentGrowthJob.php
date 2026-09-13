<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\BlogPost;
use App\Models\Recipe;
use App\Models\ContentOpportunity;
use App\Services\Integrity\IntegrityReportLog;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class AuditContentGrowthJob implements ShouldBeUniqueUntilProcessing, ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $timeout = 60;
    public int $uniqueFor = 300;
    public array $backoff = [30, 120];

    public function __construct(public readonly string $type, public readonly int $contentId)
    {
        // Persist work even when the application's default queue is synchronous.
        $this->onConnection('database')->onQueue('default')->afterCommit();
    }

    public function uniqueId(): string
    {
        return $this->type.':'.$this->contentId;
    }

    public function handle(IntegrityReportLog $reports): void
    {
        $model = match ($this->type) {
            'recipe' => Recipe::query()->find($this->contentId),
            'blog' => BlogPost::query()->find($this->contentId),
            default => null,
        };
        if ($model === null) {
            return;
        }

        $locales = [];
        $missing = 0;
        foreach ((array) config('locales.supported', []) as $locale => $settings) {
            $translated = $model->hasTranslation((string) $locale);
            $locales[$locale] = [
                'enabled' => (bool) ($settings['enabled'] ?? false),
                'translation_complete' => $translated,
                'publication_review_required' => true,
            ];
            $missing += (int) ! $translated;
            $key = 'translation-'.$this->type.'-'.$this->contentId.'-'.$locale;
            $opportunity = ContentOpportunity::query()->firstOrNew(['normalized_key' => $key]);
            if (! $translated && (! $opportunity->exists || (in_array($opportunity->status, ['open', 'resolved'], true) && $opportunity->reviewed_at === null))) {
                $opportunity->fill([
                    'kind' => 'translation_gap',
                    'title' => $locale.': '.(string) $model->getRawOriginal('title'),
                    'search_intent' => 'informational', 'topic_cluster' => 'Localization',
                    'risk_level' => 'medium', 'priority' => ($settings['enabled'] ?? false) ? 90 : 60,
                    'status' => 'open', 'related_public_ids' => [$model->public_id],
                    'evidence' => ['locale' => $locale, 'source' => 'content_translation_audit', 'publication_requires_human_review' => true],
                ])->save();
            } elseif ($translated && $opportunity->exists && $opportunity->status === 'open' && $opportunity->reviewed_at === null) {
                $opportunity->update(['status' => 'resolved']);
            }
        }

        $reports->record('seo:content-growth', $missing > 0 ? 'incomplete' : 'success', $missing, [
            'content_type' => $this->type,
            'public_id' => $model->public_id,
            'source_updated_at' => $model->updated_at?->toIso8601String(),
            'locales' => $locales,
            'scope' => 'translation_coverage_not_editorial_approval',
        ]);
    }
}
