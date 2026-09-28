<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Media;
use App\Services\Media\PublicMediaEligibilityService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class MediaCleanupObsoleteRecords extends Command
{
    protected $signature = 'media:cleanup-obsolete-records
        {--apply : Aday veritabanı kayıtlarını sil}
        {--backup= : Apply öncesinde doğrulanmış yedek manifestinin yolu}
        {--json : Makine okunabilir çıktı}';

    protected $description = 'Yetim, boş ve yer tutucu medya kayıtlarını gerçek görselleri koruyarak temizler';

    public function handle(PublicMediaEligibilityService $gate): int
    {
        $apply = (bool) $this->option('apply');
        $backup = (string) $this->option('backup');

        if ($apply && ($backup === '' || ! is_file($backup) || filesize($backup) === 0)) {
            $this->error('Silme için okunabilir, boş olmayan bir --backup manifesti zorunludur.');

            return self::FAILURE;
        }

        $reasons = ['missing_mediable' => 0, 'empty_path' => 0, 'placeholder' => 0, 'superseded' => 0];
        $candidateIds = [];
        $protected = 0;

        Media::query()->with('mediable')->orderBy('id')->chunkById(500, function ($items) use ($gate, &$reasons, &$candidateIds, &$protected): void {
            foreach ($items as $media) {
                if ($gate->isEligible($media)) {
                    $protected++;
                    continue;
                }

                $reason = match (true) {
                    $media->mediable === null => 'missing_mediable',
                    ! is_string($media->path) || trim($media->path) === '' => 'empty_path',
                    (bool) $media->is_placeholder,
                    $media->verification_status === 'branded_fallback',
                    $media->media_status === 'branded_fallback',
                    str_starts_with((string) $media->path, 'fallbacks/'),
                    str_starts_with((string) $media->path, 'placeholders/') => 'placeholder',
                    $media->media_status === 'superseded' => 'superseded',
                    default => null,
                };

                if ($reason === null) {
                    continue;
                }

                $candidateIds[] = $media->id;
                $reasons[$reason]++;
            }
        });

        $deleted = 0;
        if ($apply && $candidateIds !== []) {
            DB::transaction(function () use ($candidateIds, &$deleted): void {
                foreach (array_chunk(array_values(array_unique($candidateIds)), 500) as $ids) {
                    $deleted += Media::query()->whereKey($ids)->delete();
                }
            });
        }

        $result = [
            'dry_run' => ! $apply,
            'candidates' => count(array_unique($candidateIds)),
            'deleted' => $deleted,
            'protected_eligible' => $protected,
            'reasons' => $reasons,
            'physical_files_deleted' => 0,
        ];

        if ((bool) $this->option('json')) {
            $this->line((string) json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        } else {
            $this->info(sprintf(
                '%d aday kayıt bulundu; %d kayıt silindi; %d uygun gerçek görsel korundu. Fiziksel dosya silinmedi.',
                $result['candidates'],
                $deleted,
                $protected,
            ));
        }

        return self::SUCCESS;
    }
}
