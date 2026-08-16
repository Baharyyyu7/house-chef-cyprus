<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Services\Media\ApprovedSyntheticBlogMediaService;
use App\Services\Media\MediaOptimizationService;
use Illuminate\Console\Command;

final class ImportApprovedSyntheticBlogMedia extends Command
{
    protected $signature = 'content:import-approved-synthetic-blog-media {--source-directory= : Absolute or project-relative source directory} {--json}';

    protected $description = 'Imports user-approved synthetic blog illustrations and creates responsive public derivatives.';

    public function handle(ApprovedSyntheticBlogMediaService $service, MediaOptimizationService $optimizer): int
    {
        $directory = (string) ($this->option('source-directory') ?: resource_path('editorial/blog-synthetic'));
        if (! is_dir($directory)) {
            $this->error('BLOG_SYNTHETIC_SOURCE_DIRECTORY_MISSING');

            return self::FAILURE;
        }
        $imported = 0;
        $missing = [];
        $attempted = [];
        $media = [];
        foreach (BlogPost::query()->publiclyVisible()->orderBy('id')->get() as $post) {
            $source = rtrim($directory, '/').'/'.$post->slug.'.png';
            if (! is_file($source)) {
                $missing[] = $post->slug;

                continue;
            }
            $attempted[] = $post->slug;
            $media[] = $service->import($post, $source);
            $imported++;
        }
        $optimized = ['processed' => 0, 'skipped' => 0, 'failed' => 0];
        foreach ($media as $item) {
            if ($item->optimization_status !== 'ready') {
                $optimizer->optimizeItem($item, $optimized);
            }
        }

        // Varyant eksikliği yalnız içe aktarmayı denediğimiz yazılar için hatadır:
        // orada bozulan gerçekten işlemedir. Kaynak görseli henüz olmayan bir yazı
        // ise içerik boşluğudur ve blog-media:completion-audit'in işidir; onu deploy
        // durdurucusu saymak, sitenin kendi "onaylı görsel yoksa yer tutucu göster"
        // politikasıyla çelişiyor ve tüm sürümleri bloke ediyordu.
        $incompleteVariants = BlogPost::query()->publiclyVisible()->with('heroMedia')->get()
            ->filter(fn (BlogPost $post): bool => in_array($post->slug, $attempted, true) && ! $this->hasRequiredVariants($post))
            ->pluck('slug')->values()->all();
        $result = compact('imported', 'missing', 'optimized', 'incompleteVariants');
        $this->line((bool) $this->option('json') ? json_encode($result, JSON_THROW_ON_ERROR) : 'Imported: '.$imported.', missing: '.count($missing));

        if ($missing !== [] && ! $this->option('json')) {
            $this->warn('Kaynak görseli bekleyen yayındaki yazı: '.implode(', ', $missing));
        }

        return $incompleteVariants === [] && $optimized['failed'] === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function hasRequiredVariants(BlogPost $post): bool
    {
        $variants = is_array($post->heroMedia?->responsive_variants) ? $post->heroMedia->responsive_variants : [];
        $formats = ['jpeg'];
        if (function_exists('imagewebp')) {
            $formats[] = 'webp';
        }
        if (function_exists('imageavif')) {
            $formats[] = 'avif';
        }
        foreach (['hero', 'open_graph', 'card', 'square', 'portrait', 'thumbnail'] as $profile) {
            $variant = $variants[$profile] ?? null;
            if (! is_array($variant)) {
                return false;
            }
            foreach ($formats as $format) {
                $path = $variant[$format] ?? null;
                if (! is_string($path)) {
                    return false;
                }
            }
        }

        return true;
    }
}
