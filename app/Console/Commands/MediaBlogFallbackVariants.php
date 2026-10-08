<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Media\BlogFallbackCover;
use Illuminate\Console\Command;

final class MediaBlogFallbackVariants extends Command
{
    protected $signature = 'media:blog-fallback-variants';

    protected $description = 'Blog yedek kapağının 400/800/1600 px AVIF ve WebP varyantlarını üretir.';

    public function handle(BlogFallbackCover $kapak): int
    {
        try {
            $yazilan = $kapak->generate();
        } catch (\RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        foreach ($yazilan as $yol) {
            $this->line($yol);
        }
        $this->info(count($yazilan).' varyant yazıldı.');

        return self::SUCCESS;
    }
}
