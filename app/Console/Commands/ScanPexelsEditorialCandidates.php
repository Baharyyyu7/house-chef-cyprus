<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\RecipeMediaCandidate;
use App\Services\Media\PexelsEditorialReviewService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

final class ScanPexelsEditorialCandidates extends Command
{
    protected $signature = 'media:pexels-scan {--batch=all : 1-5 ya da all} {--contact-sheets : İnceleme HTML dosyalarını üret} {--json : Makine okunabilir çıktı}';

    protected $description = 'Pexels adaylarını yalnız private review metadata olarak tarar; dosya indirmez veya yayınlamaz';

    public function handle(PexelsEditorialReviewService $review): int
    {
        $batchOption = (string) $this->option('batch');
        $batch = $batchOption === 'all' ? null : filter_var($batchOption, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]]);
        if ($batchOption !== 'all' && $batch === false) {
            $this->error('Batch 1 ile 5 arasında olmalı.');

            return self::FAILURE;
        }
        $report = $review->scan($batch ?: null);
        if ($this->option('contact-sheets')) {
            $this->writeContactSheets();
        }
        if ($this->option('json')) {
            $this->line((string) json_encode($report, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        } else {
            $this->info('Tarif: '.$report['recipes'].' · aday: '.$report['candidates'].' · otomatik red: '.$report['auto_rejected'].' · review: '.$report['reviewable'].' · hata: '.$report['failed']);
        }

        return self::SUCCESS;
    }

    private function writeContactSheets(): void
    {
        $directory = base_path('docs/media-review/pexels');
        File::ensureDirectoryExists($directory);
        for ($batch = 1; $batch <= 5; $batch++) {
            $candidates = RecipeMediaCandidate::query()->with('recipe')->where('review_batch', $batch)->orderBy('recipe_id')->orderBy('rank')->get()->groupBy('recipe_id');
            $rows = $candidates->map(function ($items): string {
                $recipe = $items->first()->recipe;
                $cards = $items->take(3)->map(function (RecipeMediaCandidate $candidate): string {
                    $link = $candidate->pexels_page_url === null
                        ? ''
                        : ' · <a target="_blank" rel="noopener noreferrer" href="'.e($candidate->pexels_page_url).'">Pexels sayfası</a>';

                    return '<li>#'.e((string) $candidate->rank).' · skor '.e((string) $candidate->review_score).' · Pexels '.e($candidate->external_photo_id).$link.'</li>';
                })->implode('');

                return '<section><h2>'.e($recipe->title ?? 'Silinmiş tarif').'</h2><ul>'.$cards.'</ul></section>';
            })->implode("\n");
            $html = '<!doctype html><html lang="tr"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Pexels Review Batch '.$batch.'</title><style>body{font:16px system-ui;margin:2rem;background:#faf8f1;color:#173325}section{border:1px solid #ddd;border-radius:12px;padding:1rem;margin:1rem 0}h1{margin-bottom:1rem}</style><body><h1>House Chef Cyprus · Pexels Review Batch '.$batch.'</h1><p>Bu contact sheet yalnız metadata gösterir; görseller public olarak render edilmez. Seçim admin Görsel Stüdyosu üzerinden private import sonrası yapılır.</p>'.$rows.'</body></html>';
            File::put($directory.'/batch-'.str_pad((string) $batch, 2, '0', STR_PAD_LEFT).'-review.html', $html);
        }
    }
}
