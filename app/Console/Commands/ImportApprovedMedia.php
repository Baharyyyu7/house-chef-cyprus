<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Media\ApprovedMediaImportService;
use Illuminate\Console\Command;

final class ImportApprovedMedia extends Command
{
    protected $signature = 'media:import-approved
        {--dry-run : Inspect only (the default)}
        {--content-type=all : recipe, blog, or all}
        {--public-id= : Import one content public ID}
        {--limit= : Maximum manifest entries}
        {--strict : Required together with --replace-fallbacks for a public import}
        {--json : Emit machine-readable output}
        {--replace-fallbacks : Supersede fallbacks only after a successful import}
        {--manifest= : Private manifest path}
        {--preview-only : Generate the quality report without mutation}
        {--allow-controlled-resample : Permit safe resampling within the configured cap}
        {--max-upscale-factor=1.15 : Maximum permitted crop/resample scale}
        {--quality-report : Include variant and source quality details}
        {--no-publish : Force non-mutating preview mode}';

    protected $description = 'Safely imports manifest-approved private recipe or blog media; dry-run is the default.';

    public function handle(ApprovedMediaImportService $service): int
    {
        $manifest = (string) ($this->option('manifest') ?: storage_path('app/media-import/approved-media-manifest.json'));
        $raw = is_file($manifest) ? file_get_contents($manifest) : false;
        $decoded = is_string($raw) ? json_decode($raw, true) : null;
        $entries = is_array($decoded) ? ($decoded['entries'] ?? $decoded) : null;
        if (! is_array($entries)) {
            return $this->result(['status' => 'failed', 'error' => ['code' => 'MEDIA_IMPORT_MANIFEST_INVALID']], self::FAILURE);
        }
        $type = (string) $this->option('content-type');
        if (! in_array($type, ['recipe', 'blog', 'all'], true)) {
            return $this->result(['status' => 'failed', 'error' => ['code' => 'MEDIA_CONTENT_TYPE_INVALID']], self::FAILURE);
        }
        $limit = $this->option('limit');
        $entries = collect($entries)->filter(fn ($entry): bool => is_array($entry))
            ->filter(fn (array $entry): bool => $type === 'all' || ($entry['content_type'] ?? null) === $type)
            ->filter(fn (array $entry): bool => $this->option('public-id') === null || $this->option('public-id') === '' || ($entry['public_id'] ?? null) === $this->option('public-id'))
            ->when(is_numeric($limit), fn ($items) => $items->take(max(1, (int) $limit)))->values();
        $publish = (bool) $this->option('strict') && (bool) $this->option('replace-fallbacks') && ! (bool) $this->option('dry-run') && ! (bool) $this->option('preview-only') && ! (bool) $this->option('no-publish');
        $results = [];
        $failed = 0;
        $withdrawn = 0;
        foreach ($entries as $entry) {
            try {
                $results[] = $publish
                    ? $service->import($entry, (bool) $this->option('allow-controlled-resample'), (float) $this->option('max-upscale-factor'), true, true)
                    : $service->preview($entry, (bool) $this->option('allow-controlled-resample'), (float) $this->option('max-upscale-factor'));
            } catch (\Throwable $exception) {
                // Yayından çekilmiş içerik deploy'u durdurmaz: manifest sabit,
                // yayın durumu değişken. İçe aktarma yine yapılmıyor.
                if ($exception->getMessage() === 'MEDIA_CONTENT_WITHDRAWN') {
                    $withdrawn++;
                    $results[] = ['public_id' => $entry['public_id'] ?? null, 'status' => 'SKIPPED', 'reason' => ['code' => 'MEDIA_CONTENT_WITHDRAWN']];

                    continue;
                }
                $failed++;
                $results[] = ['public_id' => $entry['public_id'] ?? null, 'status' => 'FAILED', 'error' => ['code' => $exception->getMessage()]];
            }
        }
        $payload = ['status' => $failed === 0 ? 'ok' : 'failed', 'mode' => $publish ? 'import' : 'dry-run', 'count' => $entries->count(), 'failed' => $failed, 'withdrawn' => $withdrawn, 'results' => $results];

        return $this->result($payload, $failed === 0 ? self::SUCCESS : self::FAILURE);
    }

    /** @param array<string,mixed> $payload */
    private function result(array $payload, int $exit): int
    {
        if ((bool) $this->option('json')) {
            $this->line((string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        } else {
            $this->line($payload['status'] === 'ok' ? 'Media import validation completed.' : 'Media import validation failed.');
        }

        return $exit;
    }
}
