<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\BlogPost;
use App\Models\Recipe;
use App\Models\ContentOpportunity;
use App\Services\Seo\BlogIndexability;
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

    public function handle(): void
    {
        $model = match ($this->type) {
            'recipe' => Recipe::query()->find($this->contentId),
            'blog' => BlogPost::query()->find($this->contentId),
            default => null,
        };
        if ($model === null) {
            return;
        }

        // Kendi kalite kapımızın kapattığı sayfaya çeviri işi açmanın anlamı
        // yok: o sayfa aramada görünmüyor. 22 Eylül 2026'da ölçüldü: 6.495
        // açık çeviri işinin 5.207'si noindex içeriğe aitti ve gerçekten
        // yapılacak 1.288 işi gömüyordu.
        $indekslenebilir = $this->indekslenebilir($model);

        foreach ((array) config('locales.supported', []) as $locale => $settings) {
            $translated = $model->hasTranslation((string) $locale);
            $key = 'translation-'.$this->type.'-'.$this->contentId.'-'.$locale;
            $opportunity = ContentOpportunity::query()->firstOrNew(['normalized_key' => $key]);
            if (! $translated && $indekslenebilir && (! $opportunity->exists || (in_array($opportunity->status, ['open', 'resolved'], true) && $opportunity->reviewed_at === null))) {
                $opportunity->fill([
                    'kind' => 'translation_gap',
                    'title' => $locale.': '.(string) $model->getRawOriginal('title'),
                    'search_intent' => 'informational', 'topic_cluster' => 'Localization',
                    'risk_level' => 'medium', 'priority' => ($settings['enabled'] ?? false) ? 90 : 60,
                    'status' => 'open', 'related_public_ids' => [$model->public_id],
                    'evidence' => ['locale' => $locale, 'source' => 'content_translation_audit', 'publication_requires_human_review' => true],
                ])->save();
            } elseif (($translated || ! $indekslenebilir) && $opportunity->exists && $opportunity->status === 'open' && $opportunity->reviewed_at === null) {
                $opportunity->update(['status' => 'resolved']);
            }
        }

        // Burada bütünlük raporu yazılmıyor. Bu iş her içerik kaydı için bir
        // kez koşuyor; rapor yazınca tek günde 9.899 satır birikti ve
        // muhafız günlüğünü boğdu. Çeviri kapsamının manşet sayısı
        // content:translation-readiness muhafızında, tek satır olarak duruyor;
        // yapılacak işin kendisi ContentOpportunity kaydında.
    }

    /** İçerik aramada görünüyor mu? */
    private function indekslenebilir(Recipe|BlogPost $model): bool
    {
        if ($model instanceof BlogPost) {
            return ! str_contains(app(BlogIndexability::class)->robots($model), 'noindex');
        }

        return ! str_contains((string) $model->seoMetadata?->robots, 'noindex');
    }
}
