<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Http\Controllers\LlmsTxtController;
use App\Jobs\RunGrowthStageJob;
use App\Models\IntegrityReport;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class GrowthSystem
{
    public const STAGES = ['measurement', 'seo', 'opportunities', 'backlinks', 'languages', 'geo', 'indexnow'];

    public function dispatch(): int
    {
        return Cache::lock('seo:system:dispatch', 30)->block(3, function (): int {
            return DB::transaction(function (): int {
                $queued = 0;
                foreach (self::STAGES as $stage) {
                    $guard = 'seo:system:'.$stage;
                    if (IntegrityReport::query()->where('guard', $guard)->where('created_at', '>=', now()->startOfDay())->exists()) {
                        continue;
                    }
                    $report = IntegrityReport::query()->create([
                        'guard' => $guard, 'status' => 'queued',
                        'payload' => ['stage' => $stage, 'queued_at' => now()->toIso8601String()],
                    ]);
                    RunGrowthStageJob::dispatch((int) $report->id);
                    $queued++;
                }
                return $queued;
            });
        });
    }

    public function execute(string $stage): array
    {
        return match ($stage) {
            'measurement' => app(SearchConsoleMeasurementService::class)->importApi(),
            'seo' => app(SeoAuditService::class)->run('all'),
            'opportunities' => $this->opportunities(),
            'backlinks' => app(BacklinkVerificationService::class)->run(3),
            'languages' => ['locales' => app(LocaleGrowthService::class)->tablo()],
            'geo' => $this->geo(),
            'indexnow' => $this->indexNow(),
            default => throw new \InvalidArgumentException('UNKNOWN_GROWTH_STAGE'),
        };
    }

    private function opportunities(): array
    {
        app(BacklinkGrowthService::class)->discoverAndEnrich();
        app(ContentGapService::class)->refresh();
        return app(OrganicGrowthService::class)->refresh(100);
    }

    private function geo(): array
    {
        $locales = array_keys(app(LocaleUrlService::class)->enabledLocales());
        $original = app()->getLocale();
        try {
            foreach ($locales as $locale) {
                Cache::forget('seo:llms_txt_'.$locale);
                Cache::forget('seo:llms_full_txt_'.$locale);
                $controller = app(LlmsTxtController::class);
                match ($locale) {
                    'tr' => $controller->index(),
                    'en' => $controller->en(),
                    default => $controller->locale($locale),
                };
                match ($locale) {
                    'tr' => $controller->full(),
                    'en' => $controller->enFull(),
                    default => $controller->localeFull($locale),
                };
            }
        } finally {
            app()->setLocale($original);
        }
        return ['locales' => $locales, 'citation_measurement_available' => false];
    }

    private function indexNow(): array
    {
        if (! config('seo.indexnow.enabled', false)) {
            return ['status' => 'disabled', 'accepted_urls' => 0];
        }
        $result = app(IndexNowService::class)->submit();
        return ['status' => $result['success'] ? 'success' : 'incomplete', 'accepted_urls' => $result['success'] ? $result['count'] : 0, 'http_status' => $result['status']];
    }
}
