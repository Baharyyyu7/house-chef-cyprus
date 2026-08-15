<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Media;
use App\Services\Media\PhotographicContentAnalyzer;
use Illuminate\Console\Command;

final class MediaPhotographicAudit extends Command
{
    protected $signature = 'media:photographic-audit
        {--apply : Fotoğraf olmayan onaylı medyayı public yüzeyden düşür}
        {--limit=0 : Güvenli batch sınırı, 0 = tümü}
        {--json : Makine okunabilir çıktı}';

    protected $description = 'Onaylı public medyanın gerçekten fotoğraf olduğunu görüntü içeriğinden doğrular; dry-run varsayılandır.';

    public function handle(PhotographicContentAnalyzer $analyzer): int
    {
        $apply = (bool) $this->option('apply');
        $limit = (int) $this->option('limit');

        $query = Media::query()
            ->where('media_status', 'approved_real_media')
            ->where('visibility', 'public')
            ->where('disk', 'public')
            ->orderBy('id');

        if ($limit > 0) {
            $query->limit($limit);
        }

        $photographic = 0;
        $nonPhotographic = 0;
        $inconclusive = 0;
        $demoted = 0;
        $flagged = [];

        foreach ($query->get() as $media) {
            $result = $analyzer->analyze((string) $media->disk, (string) $media->path);

            if (! $result['analyzed']) {
                $inconclusive++;
                $flagged[] = ['public_id' => $media->public_id, 'path' => $media->path, 'verdict' => 'inconclusive', 'reason' => $result['reason']];

                continue;
            }

            if ($result['photographic'] === true) {
                $photographic++;
                $media->forceFill(['fidelity_status' => 'photographic'])->save();

                continue;
            }

            $nonPhotographic++;
            $flagged[] = [
                'public_id' => $media->public_id,
                'path' => $media->path,
                'verdict' => 'non_photographic',
                'energy' => $result['energy'],
                'unique_colors' => $result['unique_colors'],
            ];

            $changes = ['fidelity_status' => 'non_photographic'];
            if ($apply) {
                // Kayıt ve dosya korunur; yalnız public uygunluk geri alınır.
                $changes['media_status'] = 'no_media';
                $demoted++;
            }

            $media->forceFill($changes)->save();
        }

        $report = [
            'mode' => $apply ? 'apply' : 'dry-run',
            'audited' => $photographic + $nonPhotographic + $inconclusive,
            'photographic' => $photographic,
            'non_photographic' => $nonPhotographic,
            'inconclusive' => $inconclusive,
            'demoted' => $demoted,
            'energy_threshold' => PhotographicContentAnalyzer::ENERGY_THRESHOLD,
            'flagged' => $flagged,
        ];

        if ($this->option('json')) {
            $this->line((string) json_encode($report, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $this->info(sprintf(
            '[%s] denetlenen: %d · fotoğraf: %d · fotoğraf değil: %d · sonuçsuz: %d · düşürülen: %d',
            $report['mode'], $report['audited'], $photographic, $nonPhotographic, $inconclusive, $demoted
        ));

        if ($nonPhotographic > 0 && ! $apply) {
            $this->warn('Düşürmek için --apply ile tekrar çalıştırın.');
        }

        return self::SUCCESS;
    }
}
