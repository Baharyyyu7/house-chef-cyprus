<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\ContentOpportunity;
use App\Models\IntegrityReport;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

final class SearchConsoleMeasurementService
{
    public function property(): string
    {
        return (string) (config('seo.search_console.site_url') ?: config('integrations.google.search_console.property') ?: rtrim((string) config('app.url'), '/').'/');
    }

    public function configured(): bool
    {
        return filled(config('seo.search_console.client_id')) && filled(config('seo.search_console.client_secret')) && filled(config('seo.search_console.refresh_token'));
    }

    public function import(array $report, bool $fromApi = false): array
    {
        $data = Validator::make($report, [
            'property' => ['required', Rule::in([$this->property()])],
            'source' => ['required', Rule::in([$fromApi ? 'api_import' : 'ui_import'])],
            'search_type' => ['required', Rule::in(['web'])],
            'start_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:end_date'],
            'end_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'observed_at' => ['required', 'date', 'before_or_equal:'.now()->addMinute()->toIso8601String(), 'after_or_equal:end_date'],
            'clicks' => ['required', 'integer', 'min:0', 'max:1000000000', 'lte:impressions'],
            'impressions' => ['required', 'integer', 'min:0', 'max:1000000000'],
            'position' => ['required', 'numeric', 'min:0', 'max:1000000'],
            'queries' => ['present', 'array', 'max:1000'],
            'queries.*' => ['array:query,clicks,impressions,position'],
            'queries.*.query' => ['required', 'string', 'max:200', 'distinct'],
            'queries.*.clicks' => ['required', 'integer', 'min:0'],
            'queries.*.impressions' => ['required', 'integer', 'min:0'],
            'queries.*.position' => ['required', 'numeric', 'min:0', 'max:1000000'],
        ])->validate();
        if (Carbon::parse($data['start_date'])->diffInDays(Carbon::parse($data['end_date'])) !== 27.0) {
            throw ValidationException::withMessages(['start_date' => 'Karşılaştırılabilir ölçüm için dönem tam 28 gün olmalıdır.']);
        }
        foreach ($data['queries'] as $row) {
            if ($row['clicks'] > $row['impressions'] || $row['impressions'] > $data['impressions'] || $row['clicks'] > $data['clicks']) {
                throw ValidationException::withMessages(['queries' => 'Sorgu ölçümleri rapor toplamlarıyla tutarsız.']);
            }
        }
        $data['clicks'] = (int) $data['clicks'];
        $data['impressions'] = (int) $data['impressions'];
        $data['position'] = (float) $data['position'];
        $data['queries'] = array_map(static fn (array $row): array => [
            'query' => $row['query'], 'clicks' => (int) $row['clicks'],
            'impressions' => (int) $row['impressions'], 'position' => (float) $row['position'],
        ], $data['queries']);
        usort($data['queries'], static fn (array $a, array $b): int => strcmp($a['query'], $b['query']));
        $canonical = collect($data)->except('observed_at')->all();
        ksort($canonical);
        $fingerprint = hash('sha256', json_encode($canonical, JSON_THROW_ON_ERROR));

        return Cache::lock('seo:search-console-import', 60)->block(3, fn (): array => DB::transaction(function () use ($data, $fingerprint): array {
            $existing = IntegrityReport::query()->where('guard', 'seo:search-console-measurement')->where('payload->fingerprint', $fingerprint)->first();
            if ($existing !== null) {
                if (Carbon::parse($data['observed_at'])->gt(Carbon::parse($existing->payload['observed_at']))) {
                    $existing->update(['payload' => [...$existing->payload, 'observed_at' => $data['observed_at']]]);
                }

                return ['status' => 'unchanged', 'imported' => 0];
            }
            IntegrityReport::query()->create(['guard' => 'seo:search-console-measurement', 'status' => 'success', 'headline' => $data['clicks'], 'payload' => [...$data, 'fingerprint' => $fingerprint, 'queries_are_sample' => true]]);
            $created = 0;
            foreach ($data['queries'] as $row) {
                if ($row['impressions'] < 2 || $row['position'] < 4 || preg_match('/[@\x00-\x1f]|https?:\/\//i', $row['query'])) {
                    continue;
                }
                $queryKey = mb_strtolower(trim($row['query']));
                $legacy = ContentOpportunity::query()->where('normalized_key', 'gsc-'.hash('sha256', $queryKey))->first();
                if ($legacy !== null && IntegrityReport::query()->where('guard', 'seo:search-console-measurement')
                    ->where('payload->property', $data['property'])
                    ->where('payload->fingerprint', $legacy->evidence['measurement_fingerprint'] ?? '')->exists()) {
                    continue;
                }
                $opportunity = ContentOpportunity::query()->firstOrCreate(['normalized_key' => 'gsc-'.hash('sha256', $data['property']."\n".$queryKey)], [
                    'kind' => 'search_console_review', 'title' => $row['query'].' için mevcut sayfa ve içerik incelemesi',
                    'search_intent' => 'informational', 'topic_cluster' => 'Search Console', 'priority' => 80, 'risk_level' => 'low', 'status' => 'open',
                    'evidence' => ['source' => 'search_console', 'property' => $data['property'], 'measurement_fingerprint' => $fingerprint, 'query' => $row['query'], 'impressions' => $row['impressions'], 'clicks' => $row['clicks'], 'position' => $row['position'], 'start_date' => $data['start_date'], 'end_date' => $data['end_date'], 'delivery_policy' => 'draft_only', 'publication_requires_human_review' => true, 'cannibalization_check_required' => true],
                ]);
                $created += (int) $opportunity->wasRecentlyCreated;
            }

            return ['status' => 'success', 'imported' => 1, 'opportunities_created' => $created, 'source' => $data['source']];
        }));
    }

    public function snapshot(): array
    {
        $report = IntegrityReport::query()->where('guard', 'seo:search-console-measurement')->where('status', 'success')
            ->where('payload->property', $this->property())->where('payload->search_type', 'web')
            ->orderByDesc('payload->end_date')->orderByDesc('payload->observed_at')->latest('id')->first();
        $unknown = ['status' => 'not_connected', 'google_clicks' => null, 'google_impressions' => null, 'ranking_queries' => null, 'external_referring_domains' => null, 'api_connected' => false, 'note' => 'Search Console ölçümü henüz içe alınmadı. Bilinmeyen değer sıfır değildir.'];
        if ($report === null) {
            return $unknown;
        }
        $data = $report->getAttribute('payload');
        if (! is_array($data)) {
            return $unknown;
        }
        $stale = Carbon::parse($data['observed_at'])->lt(now()->subDays(7)) || Carbon::parse($data['end_date'])->lt(now()->subDays(10));
        $lastFailure = IntegrityReport::query()->where('guard', 'seo:search-console-api')->where('status', 'failed')
            ->where('payload->property', $this->property())->latest('id')->first();
        $failedSinceObservation = $lastFailure !== null && $lastFailure->created_at->gte(Carbon::parse($data['observed_at']));

        return [...$unknown, 'status' => $stale ? 'stale' : 'measured', 'property' => $data['property'], 'source' => $data['source'], 'api_connected' => ! $stale && ! $failedSinceObservation && (bool) config('seo.search_console.enabled') && $this->configured() && $data['source'] === 'api_import',
            'google_clicks' => (int) $data['clicks'], 'google_impressions' => (int) $data['impressions'], 'average_position' => $data['position'],
            'ctr' => $data['impressions'] > 0 ? $data['clicks'] / $data['impressions'] * 100 : 0,
            'query_sample_count' => count($data['queries']), 'start_date' => $data['start_date'], 'end_date' => $data['end_date'], 'observed_at' => $data['observed_at'],
            'note' => ($stale ? 'Eski ölçüm: ' : '').$data['start_date'].' - '.$data['end_date'].': '.$data['clicks'].' tıklama, '.$data['impressions'].' gösterim. '.($data['source'] === 'api_import' ? 'Google API.' : 'Search Console arayüz aktarımı; kalıcı API yetkisi değildir.'),
        ];
    }

    public function importApi(): array
    {
        if (! config('seo.search_console.enabled')) {
            return ['status' => 'disabled', 'imported' => 0, 'reason' => 'provider_disabled'];
        }
        if (! $this->configured()) {
            return ['status' => 'not_configured', 'imported' => 0, 'reason' => 'oauth_authorization_required'];
        }
        if (Cache::has('seo:gsc:retry-after')) {
            return ['status' => 'deferred', 'imported' => 0, 'retry_at' => Cache::get('seo:gsc:retry-after')];
        }
        $lock = Cache::lock('seo:gsc:api', 90);
        if (! $lock->get()) {
            return ['status' => 'busy', 'imported' => 0];
        }
        try {
            $token = Http::asForm()->withoutRedirecting()->connectTimeout(5)->timeout(10)->post('https://oauth2.googleapis.com/token', [
                'client_id' => config('seo.search_console.client_id'), 'client_secret' => config('seo.search_console.client_secret'),
                'refresh_token' => config('seo.search_console.refresh_token'), 'grant_type' => 'refresh_token',
            ]);
            $this->requireSuccess($token);
            if (! is_string($token->json('access_token')) || $token->json('access_token') === '') {
                throw new RuntimeException('INVALID_TOKEN_RESPONSE');
            }
            $end = now('America/Los_Angeles')->subDays(3);
            $params = ['startDate' => $end->copy()->subDays(27)->toDateString(), 'endDate' => $end->toDateString(), 'type' => 'web', 'dataState' => 'final'];
            $url = 'https://www.googleapis.com/webmasters/v3/sites/'.rawurlencode($this->property()).'/searchAnalytics/query';
            $client = Http::withToken($token->json('access_token'))->withoutRedirecting()->connectTimeout(5)->timeout(10);
            $totals = $client->post($url, $params);
            $this->requireSuccess($totals);
            $rows = $client->post($url, [...$params, 'dimensions' => ['query'], 'rowLimit' => 1000]);
            $this->requireSuccess($rows);
            $total = $totals->json('rows.0');
            if (! is_array($total) || ! is_array($rows->json('rows', []))) {
                throw new RuntimeException('MEASUREMENT_UNAVAILABLE');
            }

            return $this->import(['property' => $this->property(), 'source' => 'api_import', 'search_type' => 'web', 'start_date' => $params['startDate'], 'end_date' => $params['endDate'], 'observed_at' => now()->toIso8601String(), 'clicks' => $total['clicks'], 'impressions' => $total['impressions'], 'position' => $total['position'],
                'queries' => array_map(fn ($row) => ['query' => $row['keys'][0] ?? '', 'clicks' => $row['clicks'] ?? -1, 'impressions' => $row['impressions'] ?? -1, 'position' => $row['position'] ?? -1], $rows->json('rows', []))], true);
        } catch (Throwable) {
            IntegrityReport::query()->create(['guard' => 'seo:search-console-api', 'status' => 'failed', 'payload' => ['property' => $this->property(), 'reason' => 'API_IMPORT_FAILED', 'credentials_logged' => false, 'previous_measurement_preserved' => true]]);

            return ['status' => 'failed', 'imported' => 0, 'reason' => 'API_IMPORT_FAILED'];
        } finally {
            $lock->release();
        }
    }

    /**
     * Sayfa bazında son günlerin gösterimleri.
     *
     * Sorgu satırları hangi aramada göründüğümüzü söyler; hangi sayfanın
     * göründüğünü söylemez. Sıralanan bir sayfanın kapanması ancak sayfa
     * boyutuyla fark edilir.
     *
     * @return list<array{page: string, impressions: int, clicks: int, position: float}>
     */
    public function pageRows(int $gun = 28): array
    {
        if (! config('seo.search_console.enabled') || ! $this->configured()) {
            return [];
        }

        $token = Http::asForm()->withoutRedirecting()->connectTimeout(5)->timeout(10)->post('https://oauth2.googleapis.com/token', [
            'client_id' => config('seo.search_console.client_id'), 'client_secret' => config('seo.search_console.client_secret'),
            'refresh_token' => config('seo.search_console.refresh_token'), 'grant_type' => 'refresh_token',
        ]);
        $this->requireSuccess($token);

        $url = 'https://www.googleapis.com/webmasters/v3/sites/'.rawurlencode($this->property()).'/searchAnalytics/query';
        $cevap = Http::withToken((string) $token->json('access_token'))->withoutRedirecting()->connectTimeout(5)->timeout(15)->post($url, [
            'startDate' => now()->subDays($gun)->toDateString(), 'endDate' => now()->toDateString(),
            'type' => 'web', 'dataState' => 'all', 'dimensions' => ['page'], 'rowLimit' => 1000,
        ]);
        $this->requireSuccess($cevap);

        return array_values(array_map(static fn (array $r): array => [
            'page' => (string) ($r['keys'][0] ?? ''),
            'impressions' => (int) ($r['impressions'] ?? 0),
            'clicks' => (int) ($r['clicks'] ?? 0),
            'position' => (float) ($r['position'] ?? 0),
        ], (array) $cevap->json('rows', [])));
    }

    private function requireSuccess(Response $response): void
    {
        if (in_array($response->status(), [429, 503], true)) {
            $retry = $response->header('Retry-After');
            $date = strtotime($retry);
            $delay = is_numeric($retry) ? (int) $retry : ($date === false ? 3600 : $date - now()->timestamp);
            $until = now()->addSeconds(max(60, min(86400, $delay)));
            Cache::put('seo:gsc:retry-after', $until->toIso8601String(), $until);
        }
        if (! $response->successful()) {
            throw new RuntimeException('API_REQUEST_FAILED');
        }
    }
}
