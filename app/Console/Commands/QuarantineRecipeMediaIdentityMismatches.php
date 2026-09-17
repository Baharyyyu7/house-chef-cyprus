<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\Media;
use App\Models\Recipe;
use Illuminate\Console\Command;

final class QuarantineRecipeMediaIdentityMismatches extends Command
{
    protected $signature = 'media:quarantine-identity-mismatches
        {--apply : Public erişimi kaldırır; dosyayı fiziksel olarak silmez}
        {--limit=0 : Güvenli batch sınırı, 0 = tüm eşleşmeyenler}
        {--json : Makine okunabilir çıktı}';

    protected $description = 'Tarif kimliği başka bir tarife ait olan public medyayı karantinaya alır';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $limit = (int) $this->option('limit');
        $rows = [];
        $quarantined = 0;

        $query = Media::query()
            ->where('mediable_type', Recipe::class)
            ->where('visibility', 'public')
            ->where('media_status', 'approved_real_media')
            ->with('mediable')
            ->orderBy('id');

        foreach ($query->get() as $media) {
            $recipe = $media->mediable;
            $path = (string) $media->path;
            if (! $recipe instanceof Recipe
                || ! preg_match('#(?:^|/)media/approved-synthetic/recipe/([^/]+)/#', $path, $matches)
                || (string) ($matches[1] ?? '') === (string) $recipe->public_id) {
                continue;
            }

            if ($limit > 0 && count($rows) >= $limit) {
                break;
            }

            $row = [
                'media_public_id' => $media->public_id,
                'recipe_public_id' => $recipe->public_id,
                'recipe_title' => $recipe->title,
                'embedded_recipe_public_id' => (string) ($matches[1] ?? ''),
                'path' => $path,
                'action' => $apply ? 'quarantine' : 'report',
            ];

            if ($apply) {
                $media->forceFill([
                    'visibility' => 'private',
                    'media_status' => 'no_media',
                    'sort_order' => 90,
                    'provider_metadata' => array_merge((array) $media->provider_metadata, [
                        'quarantine_reason' => 'recipe_identity_mismatch',
                        'quarantined_at' => now()->toIso8601String(),
                    ]),
                ])->save();

                AuditLog::query()->create([
                    'action' => 'media.quarantine_identity_mismatch',
                    'auditable_type' => Media::class,
                    'auditable_id' => $media->id,
                    'metadata' => $row,
                ]);
                $quarantined++;
            }

            $rows[] = $row;
        }

        $report = [
            'mode' => $apply ? 'apply' : 'dry-run',
            'matched' => count($rows),
            'quarantined' => $quarantined,
            'physical_files_deleted' => 0,
            'human_approval_required' => true,
            'rows' => $rows,
        ];

        if ($this->option('json')) {
            $this->line((string) json_encode($report, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $this->info(sprintf(
            '[%s] kimliği uyuşmayan: %d · karantinaya alınan: %d · fiziksel silinen: 0',
            $report['mode'],
            $report['matched'],
            $report['quarantined'],
        ));

        if (! $apply && $rows !== []) {
            $this->warn('Uygulamak için --apply ile tekrar çalıştırın.');
        }

        return self::SUCCESS;
    }
}
