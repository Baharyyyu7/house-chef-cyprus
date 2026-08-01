<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Media\PexelsEditorialReviewService;
use Illuminate\Console\Command;

final class MediaCandidateScan extends Command
{
    protected $signature = 'media:candidate-scan {--provider=pexels : pexels, unsplash, eden_ai, gemini, bfl, stability veya manual} {--batch= : Pexels için güvenli review batch} {--limit=3 : Tarif başına aday sınırı} {--dry-run : Sağlayıcıya çağrı yapma} {--json : Makine okunabilir çıktı}';

    protected $description = 'Yalnız private metadata adaylarını tarar; dosya indirmez, seçmez veya public etmez';

    public function handle(PexelsEditorialReviewService $pexels): int
    {
        $provider = strtolower((string) $this->option('provider'));
        if (! in_array($provider, ['pexels', 'unsplash', 'eden_ai', 'gemini', 'bfl', 'stability', 'manual'], true)) {
            $this->error('Geçersiz provider.');

            return self::INVALID;
        }
        if ($provider !== 'pexels') {
            $result = [
                'provider' => $provider,
                'status' => 'manual_recipe_scope_required',
                'safe_message' => $provider === 'unsplash'
                    ? 'Unsplash araması tarif bazında mevcut editoryal akıştan yapılır; toplu otomasyon public etmez.'
                    : 'Bu sağlayıcı için toplu aday taraması etkin değildir; yalnız açık insan onaylı editoryal akış kullanılabilir.',
            ];
        } elseif ($this->option('dry-run')) {
            $result = ['provider' => 'pexels', 'status' => 'dry_run', 'safe_message' => 'Pexels çağrısı yapılmadı; private metadata dışında işlem uygulanmaz.'];
        } else {
            $batch = $this->option('batch');
            $result = array_merge(['provider' => 'pexels'], $pexels->scan($batch === null ? null : max(1, (int) $batch), max(1, min(5, (int) $this->option('limit')))));
        }
        $this->option('json') ? $this->line((string) json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) : $this->info('Candidate scan: '.($result['status'] ?? 'tamamlandı'));

        return self::SUCCESS;
    }
}
