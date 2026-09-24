<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\ContentOpportunity;
use App\Models\IntegrityReport;
use App\Models\SeoBacklinkCandidate;
use App\Models\SeoIssue;
use App\Models\ZeroResultSearch;
use App\Services\Integrity\IntegrityReportLog;
use App\Services\Security\SafeUrlValidator;
use GuzzleHttp\Psr7\UriResolver;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

final class OrganicGrowthService
{
    public const MAX_LIMIT = 1000;

    public const MAX_IMPORT_ROWS = 10000;

    public const MAX_IMPORT_KB = 10240;

    public function refresh(int $limit = 1000): array
    {
        $limit = max(1, min(self::MAX_LIMIT, $limit));
        $lock = Cache::lock('seo:growth-refresh', 300);
        if (! $lock->get()) {
            return ['status' => 'busy', 'limit' => $limit];
        }

        try {
            return DB::transaction(function () use ($limit): array {
                $result = [
                    'status' => 'success',
                    'limit' => $limit,
                    ...$this->reconcileIssueWork($limit),
                    'opportunities_created' => $this->createOpportunities($limit),
                    ...$this->deriveCandidates($limit),
                    'generated_at' => now()->toIso8601String(),
                    'publishes_content' => false,
                    'external_http_requests' => 0,
                    'outreach_sent' => 0,
                ];
                app(IntegrityReportLog::class)->record('seo:growth-refresh', 'success', $result['opportunities_created'] + $result['candidates_created'], $result);

                return $result;
            });
        } catch (Throwable $exception) {
            app(IntegrityReportLog::class)->record('seo:growth-refresh', 'failed', null, [
                'limit' => $limit,
                'error_code' => 'GROWTH_REFRESH_FAILED',
                'opportunities_created' => 0,
                'candidates_created' => 0,
                'rolled_back' => true,
            ]);

            throw $exception;
        } finally {
            $lock->release();
        }
    }

    public function snapshot(): array
    {
        $counts = array_fill_keys(array_keys(SeoBacklinkCandidate::STATUSES), 0);
        foreach (SeoBacklinkCandidate::query()->select('status')->selectRaw('COUNT(*) AS aggregate')->groupBy('status')->toBase()->get() as $row) {
            $counts[$row->status] = (int) $row->aggregate;
        }

        return [
            'backlinks' => $counts,
            'research_candidates' => $counts['candidate'] + $counts['reviewing'] + $counts['contacted'],
            'observed_links' => SeoBacklinkCandidate::query()->where('status', 'observed')
                ->whereNotNull('reviewed_at')->where('source_url', '<>', '')->where('review_note', '<>', '')->count(),
            'machine_verified_links' => SeoBacklinkCandidate::query()->where('status', '<>', 'rejected')->where('check_status', 'present')->where('checked_at', '>=', now()->subDays(7))->count(),
            'open_issues' => SeoIssue::query()->whereNull('resolved_at')->count(),
            'open_opportunities' => ContentOpportunity::query()->where('status', 'open')->count(),
            'latest_run' => IntegrityReport::query()->where('guard', 'seo:growth-refresh')->latest('id')->first(),
            'organic_metrics' => app(SearchConsoleMeasurementService::class)->snapshot(),
        ];
    }

    public function importCsv(UploadedFile $file, string $actorPublicId): array
    {
        if (! $file->isValid() || $file->getSize() > self::MAX_IMPORT_KB * 1024) {
            throw ValidationException::withMessages(['csv' => 'CSV en fazla 10 MB olabilir.']);
        }

        $stream = $file->openFile('rb');
        $header = $stream->fgetcsv(',', '"', '');
        if (! is_array($header)) {
            throw ValidationException::withMessages(['csv' => 'CSV başlık satırı eksik.']);
        }
        $header = array_map(fn ($value): string => trim((string) $value), $header);
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        if (count(array_unique($header)) !== count($header)
            || array_diff(['source_url', 'target_url'], $header) !== []
            || array_diff($header, ['source_url', 'target_url', 'evidence']) !== []) {
            throw ValidationException::withMessages(['csv' => 'Başlıklar source_url,target_url ve isteğe bağlı evidence olmalıdır. Tüm kayıtlar araştırma adayı olarak alınır.']);
        }

        $checksum = hash_file('sha256', $file->getPathname());

        return DB::transaction(function () use ($stream, $header, $checksum, $actorPublicId): array {
            $created = 0;
            $duplicates = 0;
            $rows = 0;
            while (! $stream->eof()) {
                $values = $stream->fgetcsv(',', '"', '');
                if ($values === false || $values === [null]) {
                    continue;
                }
                $rows++;
                if ($rows > self::MAX_IMPORT_ROWS) {
                    throw ValidationException::withMessages(['csv' => 'CSV en fazla 10.000 veri satırı içerebilir. Hiçbir satır kaydedilmedi.']);
                }
                if (count($values) !== count($header)) {
                    throw ValidationException::withMessages(['csv' => "Veri satırı {$rows}: sütun sayısı başlıklarla eşleşmiyor."]);
                }
                $row = array_combine($header, array_map(fn ($value): string => trim((string) $value), $values));
                $pair = $this->candidateUrls($row['source_url'], $row['target_url']);
                $note = $row['evidence'] ?? '';
                if ($pair === null || ! mb_check_encoding($note, 'UTF-8') || mb_strlen($note) > 2000 || str_contains($note, "\0")) {
                    throw ValidationException::withMessages(['csv' => "Veri satırı {$rows}: herkese açık HTTP(S) kaynak, sitenin kendi kökeninde hedef ve en fazla 2.000 karakter kanıt gerekli. Hiçbir satır kaydedilmedi."]);
                }
                $candidate = SeoBacklinkCandidate::query()->firstOrCreate(['deduplication_hash' => $pair['deduplication_hash']], [
                    ...$pair,
                    'status' => 'candidate',
                    'evidence' => [
                        'source' => 'admin_csv', 'file_sha256' => $checksum, 'data_row' => $rows,
                        'imported_by_public_id' => $actorPublicId, 'note' => $note,
                        'collected_at' => now()->toIso8601String(), 'independently_verified' => false,
                    ],
                ]);
                $candidate->wasRecentlyCreated ? $created++ : $duplicates++;
            }
            if ($rows === 0) {
                throw ValidationException::withMessages(['csv' => 'CSV en az bir veri satırı içermelidir.']);
            }

            return compact('rows', 'created', 'duplicates');
        });
    }

    public function candidateUrls(string $sourceUrl, string $targetUrl): ?array
    {
        $source = $this->publicUrl($sourceUrl);
        $target = $this->publicUrl($targetUrl);
        $site = $this->publicUrl((string) config('app.url'));
        if ($source === null || $target === null || $site === null || $target['origin'] !== $site['origin']) {
            return null;
        }
        $siteHost = preg_replace('/^www\./', '', $site['host']);
        if ($source['host'] === $siteHost || str_ends_with($source['host'], '.'.$siteHost)) {
            return null;
        }
        if (preg_match('/(?:foodblog|placeholder)\d+/i', $source['host'])) {
            return null;
        }

        return [
            'source_url' => $source['url'],
            'source_host' => $source['host'],
            'target_url' => $target['url'],
            'deduplication_hash' => hash('sha256', $source['url']."\n".$target['url']),
        ];
    }

    private function publicUrl(string $url): ?array
    {
        $url = trim($url);
        if (strlen($url) > 2048 || preg_match('/[^\x21-\x7e]|\\\\|%(?:0[0-9a-f]|1[0-9a-f]|20|5c|7f)/i', $url)
            || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return null;
        }
        try {
            app(SafeUrlValidator::class)->validate($url);
        } catch (ValidationException) {
            return null;
        }

        $parts = parse_url($url);
        $host = strtolower($parts['host']);
        $ip = trim($host, '[]');
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_GLOBAL_RANGE)) {
                return null;
            }
        } elseif (strlen($host) > 253
            || ! preg_match('/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+(?:[a-z]{2,63}|xn--[a-z0-9-]+)$/D', $host)
            || preg_match('/\.(?:localhost|local|internal|intranet|lan|home|corp|test|invalid|example|arpa|onion)$/D', $host)) {
            return null;
        }

        $scheme = strtolower($parts['scheme']);
        $port = $parts['port'] ?? ($scheme === 'https' ? 443 : 80);
        $origin = $scheme.'://'.$host.($port === ($scheme === 'https' ? 443 : 80) ? '' : ':'.$port);
        $path = preg_replace_callback('/%[0-9a-f]{2}/i', static function (array $match): string {
            $character = rawurldecode($match[0]);

            return preg_match('/[a-z0-9._~-]/i', $character) ? $character : strtoupper($match[0]);
        }, $parts['path'] ?? '/');
        $normalized = $origin.UriResolver::removeDotSegments($path).(isset($parts['query']) ? '?'.$parts['query'] : '');

        return ['url' => $normalized, 'host' => $host, 'origin' => $origin];
    }

    private function reconcileIssueWork(int $limit): array
    {
        $resolved = 0;
        $reopened = 0;
        $priority = "CASE seo_issues.severity WHEN 'critical' THEN 100 WHEN 'error' THEN 90 WHEN 'warning' THEN 75 ELSE 50 END";
        $work = ContentOpportunity::query()
            ->join('seo_issues', 'seo_issues.fingerprint', '=', DB::raw('SUBSTR(content_opportunities.normalized_key, 14)'))
            ->where('content_opportunities.normalized_key', 'like', 'growth-issue-%')
            ->where('content_opportunities.kind', 'seo_issue_review')
            ->whereNull('content_opportunities.reviewed_at')
            ->whereIn('content_opportunities.status', ['open', 'resolved'])
            ->where(function ($query) use ($priority): void {
                $query->where(fn ($query) => $query->where('content_opportunities.status', 'open')->whereNotNull('seo_issues.resolved_at'))
                    ->orWhere(fn ($query) => $query->where('content_opportunities.status', 'resolved')->whereNull('seo_issues.resolved_at'))
                    ->orWhereRaw('content_opportunities.priority <> '.$priority);
            })
            ->orderBy('content_opportunities.id')->limit($limit)
            ->select('content_opportunities.*', 'seo_issues.resolved_at as issue_resolved_at', 'seo_issues.reopened_at as issue_reopened_at', 'seo_issues.severity as issue_severity')
            ->lockForUpdate()->get();

        foreach ($work as $opportunity) {
            $status = $opportunity->issue_resolved_at === null ? 'open' : 'resolved';
            $resolved += (int) ($status === 'resolved' && $opportunity->status !== $status);
            $reopened += (int) ($status === 'open' && $opportunity->status !== $status);
            $opportunity->update([
                'status' => $status,
                'priority' => match ($opportunity->issue_severity) {
                    'critical' => 100, 'error' => 90, 'warning' => 75, default => 50,
                },
                'risk_level' => in_array($opportunity->issue_severity, ['critical', 'error'], true) ? 'high' : 'low',
                'evidence' => [...($opportunity->evidence ?? []),
                    'severity' => $opportunity->issue_severity,
                    'issue_resolved_at' => $opportunity->issue_resolved_at,
                    'issue_reopened_at' => $opportunity->issue_reopened_at,
                    'reconciled_at' => now()->toIso8601String(),
                ],
            ]);
        }

        return ['opportunities_resolved' => $resolved, 'opportunities_reopened' => $reopened];
    }

    private function createOpportunities(int $limit): int
    {
        $created = 0;
        $issues = SeoIssue::query()->whereNull('resolved_at')
            ->whereNotIn('fingerprint', ContentOpportunity::query()->where('normalized_key', 'like', 'growth-issue-%')->selectRaw('SUBSTR(normalized_key, 14)'))
            ->orderByRaw("CASE severity WHEN 'critical' THEN 0 WHEN 'error' THEN 1 WHEN 'warning' THEN 2 ELSE 3 END")
            ->orderBy('first_detected_at')->orderBy('id')->limit($limit)->get();
        foreach ($issues as $issue) {
            $opportunity = ContentOpportunity::query()->firstOrCreate(['normalized_key' => 'growth-issue-'.$issue->fingerprint], [
                'kind' => 'seo_issue_review', 'title' => Str::limit($issue->message, 255, ''),
                'topic_cluster' => 'Teknik SEO', 'priority' => match ($issue->severity) {
                    'critical' => 100, 'error' => 90, 'warning' => 75, default => 50,
                },
                'risk_level' => in_array($issue->severity, ['critical', 'error'], true) ? 'high' : 'low',
                'status' => 'open', 'related_public_ids' => array_values(array_filter([$issue->entity_public_id])),
                'evidence' => [
                    'source' => 'seo_issue', 'issue_fingerprint' => $issue->fingerprint,
                    'issue_url' => $issue->url, 'category' => $issue->category, 'severity' => $issue->severity,
                    'entity_public_id' => $issue->entity_public_id, 'entity_type' => $issue->entity_type,
                    'last_detected_at' => $issue->last_detected_at ? Carbon::parse($issue->last_detected_at)->toIso8601String() : null,
                    'collected_at' => now()->toIso8601String(), 'publication_requires_human_review' => true, 'delivery_policy' => 'draft_only',
                ],
            ]);
            $created += (int) $opportunity->wasRecentlyCreated;
        }

        if ($created >= $limit) {
            return $created;
        }
        $enabledLocales = array_keys(app(LocaleUrlService::class)->enabledLocales());
        $searches = ZeroResultSearch::query()->whereNull('resolved_at')->whereIn('locale', $enabledLocales)
            ->where('search_count', '>=', max(1, (int) config('seo.audit.minimum_zero_result_searches', 5)))
            ->whereNotIn('normalized_query', ContentOpportunity::query()->where('normalized_key', 'like', 'zero-%')->selectRaw('SUBSTR(normalized_key, 6)'))
            ->orderByDesc('search_count')->orderBy('id')->limit($limit - $created)->get();
        foreach ($searches as $search) {
            $opportunity = ContentOpportunity::query()->firstOrCreate(['normalized_key' => 'zero-'.$search->normalized_query], [
                'kind' => 'zero_result_question', 'title' => Str::limit('"'.$search->normalized_query.'" araması için içerik incelemesi', 255, ''),
                'search_intent' => 'informational', 'topic_cluster' => 'Kullanıcı Soruları',
                'priority' => min(85, 50 + $search->search_count), 'risk_level' => 'low', 'status' => 'open',
                'evidence' => [
                    'source' => 'zero_result_search', 'query' => $search->normalized_query, 'locale' => $search->locale,
                    'anonymous_search_count' => $search->search_count, 'last_searched_at' => $search->last_searched_at ? Carbon::parse($search->last_searched_at)->toIso8601String() : null,
                    'collected_at' => now()->toIso8601String(), 'publication_requires_human_review' => true, 'delivery_policy' => 'draft_only',
                ],
            ]);
            $created += (int) $opportunity->wasRecentlyCreated;
        }

        return $created;
    }

    private function deriveCandidates(int $limit): array
    {
        $recipes = DB::table('recipes')->where('status', 'published')->where('verification_status', 'verified')->whereNotNull('source_url')
            ->whereNull('deleted_at')
            ->selectRaw('0 AS source_kind, id AS source_id, public_id AS recipe_public_id, slug, source_url, NULL AS provenance_public_id');
        $provenances = DB::table('recipe_provenances AS p')->join('recipes AS r', 'r.id', '=', 'p.recipe_id')
            ->where('r.status', 'published')->where('r.verification_status', 'verified')
            ->whereNull('r.deleted_at')
            ->where('p.is_active', true)->where('p.is_ai_generated', false)->whereNotNull('p.source_url')
            ->selectRaw('1 AS source_kind, p.id AS source_id, r.public_id AS recipe_public_id, r.slug, p.source_url, p.public_id AS provenance_public_id');
        $sources = DB::query()->fromSub($recipes->unionAll($provenances), 'sources');
        $previous = IntegrityReport::query()->where('guard', 'seo:growth-refresh')->where('status', 'success')->latest('id')->first();
        $cursor = $previous?->payload['source_cursor'] ?? null;
        $pending = clone $sources;
        if (is_array($cursor)) {
            $pending->where(fn ($query) => $query->where('source_kind', '>', $cursor['kind'])
                ->orWhere(fn ($query) => $query->where('source_kind', $cursor['kind'])->where('source_id', '>', $cursor['id'])));
        }
        $rows = $pending->orderBy('source_kind')->orderBy('source_id')->limit($limit)->get();
        // Rotate through stored provenance so unsafe or previously reviewed rows cannot starve later sources.
        if ($rows->isEmpty() && $cursor !== null) {
            $rows = $sources->orderBy('source_kind')->orderBy('source_id')->limit($limit)->get();
        }

        $created = 0;
        $skipped = 0;
        foreach ($rows as $row) {
            $target = rtrim((string) config('app.url'), '/').route('recipes.show', ['recipe' => $row->slug], false);
            $pair = $this->candidateUrls($row->source_url, $target);
            $cursor = ['kind' => (int) $row->source_kind, 'id' => (int) $row->source_id];
            if ($pair === null) {
                $skipped++;

                continue;
            }
            $candidate = SeoBacklinkCandidate::query()->firstOrCreate(['deduplication_hash' => $pair['deduplication_hash']], [
                ...$pair, 'status' => 'candidate',
                'evidence' => [
                    'source' => $row->source_kind == 0 ? 'recipe_source' : 'recipe_provenance',
                    'recipe_public_id' => $row->recipe_public_id, 'provenance_public_id' => $row->provenance_public_id,
                    'collected_at' => now()->toIso8601String(), 'independently_verified' => false,
                ],
            ]);
            $created += (int) $candidate->wasRecentlyCreated;
        }

        return ['sources_scanned' => $rows->count(), 'sources_skipped' => $skipped, 'candidates_created' => $created, 'source_cursor' => $cursor];
    }
}
