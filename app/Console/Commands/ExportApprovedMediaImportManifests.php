<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Models\Recipe;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/** Creates private, deterministic intake manifests; it never approves or publishes media. */
final class ExportApprovedMediaImportManifests extends Command
{
    protected $signature = 'media:export-import-manifests {--json : Emit the result as JSON}';

    protected $description = 'Exports private media intake manifests for published recipes and blogs.';

    public function handle(): int
    {
        $recipes = Recipe::query()
            ->where('status', 'published')
            ->where('verification_status', 'verified')
            ->with(['category:id,name', 'cuisine:id,name', 'recipeIngredients.ingredient:id,name', 'media'])
            ->orderBy('id')
            ->get();
        $blogs = BlogPost::query()
            ->publiclyVisible()
            ->with(['category:id,name', 'heroMedia'])
            ->orderBy('id')
            ->get();

        $recipeManifest = [
            'version' => 1,
            'generated_at' => now()->toIso8601String(),
            'content_type' => 'recipe',
            'entry_count' => $recipes->count(),
            'entries' => $recipes->map(function (Recipe $recipe): array {
                $current = $recipe->media->sortBy('sort_order')->first();

                return [
                    'recipe_public_id' => $recipe->public_id,
                    'database_id' => $recipe->id,
                    'title' => $recipe->title,
                    'slug' => $recipe->slug,
                    'category' => $recipe->category?->name,
                    'cuisine' => $recipe->cuisine?->name,
                    'cooking_method' => is_array($recipe->content_factory_metadata) ? ($recipe->content_factory_metadata['cooking_method'] ?? null) : null,
                    'primary_ingredients' => $recipe->recipeIngredients->sortBy('sort_order')->take(6)->map(fn ($item): ?string => $item->ingredient?->name)->filter()->values()->all(),
                    'expected_source_filenames' => $this->sourceNames('recipe', $recipe->slug),
                    'source_checksum_sha256' => null,
                    'source_mime' => null,
                    'identity_match' => false,
                    'current_media_public_id' => $current?->public_id,
                    'current_media_type' => $current?->kind,
                    'current_fallback_state' => $current?->verification_status === 'branded_fallback',
                    'target_media_purpose' => 'recipe_hero_and_card',
                    'alt_text_draft' => $recipe->title.' için servis edilmiş yemek',
                    'required_variants' => ['hero', 'wide', 'card', 'square', 'portrait', 'open_graph', 'thumbnail'],
                    'approval_state' => 'READY_FOR_REVIEW',
                    'reviewer' => null,
                    'provenance' => null,
                    'import_state' => 'AWAITING_SOURCE',
                    'deployment_state' => 'NOT_DEPLOYED',
                    'public_verification_state' => 'NOT_VERIFIED',
                ];
            })->all(),
        ];
        $blogManifest = [
            'version' => 1,
            'generated_at' => now()->toIso8601String(),
            'content_type' => 'blog',
            'entry_count' => $blogs->count(),
            'entries' => $blogs->map(function (BlogPost $post): array {
                $current = $post->heroMedia;

                return [
                    'blog_public_id' => $post->public_id,
                    'database_id' => $post->id,
                    'title' => $post->title,
                    'slug' => $post->slug,
                    'topic' => $post->category?->name,
                    'article_risk_level' => $post->fact_check_required ? 'review_required' : 'standard',
                    'expected_source_filenames' => $this->sourceNames('blog', $post->slug),
                    'source_checksum_sha256' => null,
                    'source_mime' => null,
                    'current_media_public_id' => $current?->public_id,
                    'current_fallback_state' => $current?->verification_status === 'branded_fallback',
                    'target_media_purpose' => 'blog_hero_and_card',
                    'alt_text_draft' => $post->title.' başlığıyla ilgili editoryal illüstrasyon',
                    'required_variants' => ['hero', 'card', 'square', 'portrait', 'open_graph', 'thumbnail'],
                    'approval_state' => 'READY_FOR_REVIEW',
                    'reviewer' => null,
                    'provenance' => null,
                    'import_state' => 'AWAITING_SOURCE',
                    'deployment_state' => 'NOT_DEPLOYED',
                    'public_verification_state' => 'NOT_VERIFIED',
                ];
            })->all(),
        ];

        Storage::disk('local')->put('media-import/recipe-media-manifest.json', json_encode($recipeManifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)."\n");
        Storage::disk('local')->put('media-import/blog-media-manifest.json', json_encode($blogManifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)."\n");

        $result = ['recipes' => $recipes->count(), 'blogs' => $blogs->count(), 'state' => 'AWAITING_APPROVED_SOURCES'];
        $this->line($this->option('json') ? json_encode($result, JSON_THROW_ON_ERROR) : 'Private manifests exported: recipes='.$recipes->count().', blogs='.$blogs->count());

        return self::SUCCESS;
    }

    /** @return list<string> */
    private function sourceNames(string $type, string $slug): array
    {
        return ["{$type}--{$slug}.jpg", "{$type}--{$slug}.png", "{$type}--{$slug}.webp"];
    }
}
