<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Media\BrandedRecipeFallbackService;
use Illuminate\Console\Command;

final class GenerateBrandedRecipeFallbacks extends Command
{
    protected $signature = 'recipe-images:generate-fallbacks';

    protected $description = 'Yayınlanmış tarifler için benzersiz markalı fallback görsellerini üretir';

    public function handle(BrandedRecipeFallbackService $fallbacks): int
    {
        $result = $fallbacks->synchronize();
        $this->info('Markalı fallback: '.$result['created'].' oluşturuldu, '.$result['updated'].' güncellendi.');

        return self::SUCCESS;
    }
}
