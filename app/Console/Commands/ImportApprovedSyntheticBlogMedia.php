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
        $imported = 0; $missing = [];
        foreach (BlogPost::query()->publiclyVisible()->orderBy('id')->get() as $post) {
            $source = rtrim($directory, '/').'/'.$post->slug.'.png';
            if (! is_file($source)) { $missing[] = $post->slug; continue; }
            $service->import($post, $source); $imported++;
        }
        $optimized = $optimizer->optimize(100);
        $incompleteVariants = BlogPost::query()->publiclyVisible()->with('heroMedia')->get()
            ->filter(fn (BlogPost $post): bool => ! $this->hasRequiredVariants($post))
            ->pluck('slug')->values()->all();
        $result = compact('imported', 'missing', 'optimized', 'incompleteVariants');
        $this->line((bool) $this->option('json') ? json_encode($result, JSON_THROW_ON_ERROR) : 'Imported: '.$imported.', missing: '.count($missing));
        return $missing === [] && $incompleteVariants === [] ? self::SUCCESS : self::FAILURE;
    }

    private function hasRequiredVariants(BlogPost $post): bool
    {
        $variants = (array) $post->heroMedia?->responsive_variants;
        foreach (['hero', 'open_graph', 'card', 'square'] as $profile) {
            $variant = $variants[$profile] ?? null;
            if (! is_array($variant) || ! is_string($variant['jpeg'] ?? null) || ! is_string($variant['webp'] ?? null) || ! is_string($variant['avif'] ?? null)) {
                return false;
            }
        }

        return true;
    }
}
