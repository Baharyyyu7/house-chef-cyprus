<?php

declare(strict_types=1);

namespace App\Services\Localization;

use App\Models\BlogPost;
use App\Models\BlogPostTranslation;
use App\Models\Recipe;
use App\Models\RecipeTranslation;
use Illuminate\Support\Facades\DB;

/**
 * Imports versioned, editorially written translations from
 * resources/translations/{locale}/*.php into the translation tables.
 *
 * Catalogue files are keyed by the Turkish slug, so an entry only attaches to
 * a recipe or post that already exists. Missing sources are reported, never
 * created, and translations never change the Turkish record.
 */
final class ContentTranslationImporter
{
    /** @return array{locale:string,recipes:array{imported:int,unchanged:int,missing_source:list<string>,invalid:list<string>},blog:array{imported:int,unchanged:int,missing_source:list<string>,invalid:list<string>}} */
    public function import(string $locale = 'en', bool $dryRun = false): array
    {
        return [
            'locale' => $locale,
            'recipes' => $this->importRecipes($locale, $this->catalogue($locale, 'recipes'), $dryRun),
            'blog' => $this->importPosts($locale, $this->catalogue($locale, 'blog'), $dryRun),
        ];
    }

    /** @return array<string,array<string,mixed>> */
    public function catalogue(string $locale, string $type): array
    {
        $path = resource_path("translations/{$locale}/{$type}.php");
        if (! is_file($path)) {
            return [];
        }
        $entries = require $path;

        return is_array($entries) ? $entries : [];
    }

    /**
     * @param  array<string,array<string,mixed>>  $entries
     * @return array{imported:int,unchanged:int,missing_source:list<string>,invalid:list<string>}
     */
    private function importRecipes(string $locale, array $entries, bool $dryRun): array
    {
        $result = ['imported' => 0, 'unchanged' => 0, 'missing_source' => [], 'invalid' => []];
        $sources = Recipe::query()->whereIn('slug', array_keys($entries))->pluck('id', 'slug');

        foreach ($entries as $sourceSlug => $entry) {
            $attributes = $this->recipeAttributes($entry);
            if ($attributes === null) {
                $result['invalid'][] = (string) $sourceSlug;

                continue;
            }
            $recipeId = $sources[$sourceSlug] ?? null;
            if ($recipeId === null) {
                $result['missing_source'][] = (string) $sourceSlug;

                continue;
            }
            $slugTaken = RecipeTranslation::query()->where('locale', $locale)->where('slug', $attributes['slug'])->where('recipe_id', '!=', $recipeId)->exists();
            if ($slugTaken) {
                $result['invalid'][] = (string) $sourceSlug;

                continue;
            }
            $hash = hash('sha256', json_encode($attributes, JSON_THROW_ON_ERROR));
            $existing = RecipeTranslation::query()->where('recipe_id', $recipeId)->where('locale', $locale)->first();
            if ($existing?->source_hash === $hash) {
                $result['unchanged']++;

                continue;
            }
            if (! $dryRun) {
                DB::transaction(fn () => RecipeTranslation::query()->updateOrCreate(
                    ['recipe_id' => $recipeId, 'locale' => $locale],
                    $attributes + ['status' => 'published', 'source_hash' => $hash, 'translated_at' => now()],
                ));
            }
            $result['imported']++;
        }

        return $result;
    }

    /**
     * @param  array<string,array<string,mixed>>  $entries
     * @return array{imported:int,unchanged:int,missing_source:list<string>,invalid:list<string>}
     */
    private function importPosts(string $locale, array $entries, bool $dryRun): array
    {
        $result = ['imported' => 0, 'unchanged' => 0, 'missing_source' => [], 'invalid' => []];
        $sources = BlogPost::query()->whereIn('slug', array_keys($entries))->pluck('id', 'slug');

        foreach ($entries as $sourceSlug => $entry) {
            $attributes = $this->postAttributes($entry);
            if ($attributes === null) {
                $result['invalid'][] = (string) $sourceSlug;

                continue;
            }
            $postId = $sources[$sourceSlug] ?? null;
            if ($postId === null) {
                $result['missing_source'][] = (string) $sourceSlug;

                continue;
            }
            $slugTaken = BlogPostTranslation::query()->where('locale', $locale)->where('slug', $attributes['slug'])->where('blog_post_id', '!=', $postId)->exists();
            if ($slugTaken) {
                $result['invalid'][] = (string) $sourceSlug;

                continue;
            }
            $hash = hash('sha256', json_encode($attributes, JSON_THROW_ON_ERROR));
            $existing = BlogPostTranslation::query()->where('blog_post_id', $postId)->where('locale', $locale)->first();
            if ($existing?->source_hash === $hash) {
                $result['unchanged']++;

                continue;
            }
            if (! $dryRun) {
                DB::transaction(fn () => BlogPostTranslation::query()->updateOrCreate(
                    ['blog_post_id' => $postId, 'locale' => $locale],
                    $attributes + ['status' => 'published', 'source_hash' => $hash, 'translated_at' => now()],
                ));
            }
            $result['imported']++;
        }

        return $result;
    }

    /**
     * @param  array<string,mixed>  $entry
     * @return array<string,mixed>|null
     */
    public function recipeAttributes(array $entry): ?array
    {
        $ingredients = $this->ingredients($entry['ingredients'] ?? null);
        $steps = $this->steps($entry['steps'] ?? null);
        if (! $this->validSlug($entry['slug'] ?? null) || ! filled($entry['title'] ?? null) || ! filled($entry['excerpt'] ?? null) || $ingredients === [] || $steps === []) {
            return null;
        }
        [$prep, $cook, $rest] = array_pad(array_map('intval', (array) ($entry['times'] ?? [])), 3, null);

        return [
            'slug' => (string) $entry['slug'],
            'title' => (string) $entry['title'],
            'excerpt' => (string) $entry['excerpt'],
            'description' => $this->text($entry['description'] ?? null),
            'cuisine_label' => $this->text($entry['cuisine'] ?? null),
            'category_label' => $this->text($entry['category'] ?? null),
            'difficulty_label' => $this->text($entry['difficulty'] ?? null),
            'prep_minutes' => $prep,
            'cook_minutes' => $cook,
            'rest_minutes' => $rest,
            'servings' => isset($entry['servings']) ? max(1, (int) $entry['servings']) : null,
            'ingredients' => $ingredients,
            'steps' => $steps,
            'equipment' => $this->strings($entry['equipment'] ?? []),
            'tips' => $this->strings($entry['tips'] ?? []),
            'common_mistakes' => $this->strings($entry['mistakes'] ?? []),
            'keywords' => $this->strings($entry['keywords'] ?? []),
            'serving_suggestions' => $this->text($entry['serving'] ?? null),
            'storage_notes' => $this->text($entry['storage'] ?? null),
            'freezing_notes' => $this->text($entry['freezing'] ?? null),
            'reheating_notes' => $this->text($entry['reheating'] ?? null),
            'image_alt' => $this->text($entry['image_alt'] ?? null),
            'meta_title' => $this->text($entry['meta_title'] ?? null),
            'meta_description' => $this->text($entry['meta_description'] ?? null),
        ];
    }

    /**
     * @param  array<string,mixed>  $entry
     * @return array<string,mixed>|null
     */
    public function postAttributes(array $entry): ?array
    {
        if (! $this->validSlug($entry['slug'] ?? null) || ! filled($entry['title'] ?? null) || ! filled($entry['body'] ?? null)) {
            return null;
        }
        $faq = collect((array) ($entry['faq'] ?? []))
            ->filter(fn (mixed $item): bool => is_array($item) && filled($item[0] ?? $item['question'] ?? null) && filled($item[1] ?? $item['answer'] ?? null))
            ->map(fn (array $item): array => ['question' => (string) ($item['question'] ?? $item[0]), 'answer' => (string) ($item['answer'] ?? $item[1])])
            ->values()->all();

        return [
            'slug' => (string) $entry['slug'],
            'title' => (string) $entry['title'],
            'excerpt' => $this->text($entry['excerpt'] ?? null),
            'body' => trim((string) $entry['body']),
            'category_label' => $this->text($entry['category'] ?? null),
            'keywords' => $this->strings($entry['keywords'] ?? []),
            'faq_blocks' => $faq,
            'meta_title' => $this->text($entry['meta_title'] ?? null),
            'meta_description' => $this->text($entry['meta_description'] ?? null),
        ];
    }

    /** @return list<array{group:string|null,text:string}> */
    private function ingredients(mixed $value): array
    {
        $lines = [];
        foreach ((array) $value as $group => $items) {
            if (is_string($items)) {
                $lines[] = ['group' => null, 'text' => trim($items)];

                continue;
            }
            foreach ($this->strings($items) as $item) {
                $lines[] = ['group' => is_string($group) ? $group : null, 'text' => $item];
            }
        }

        return array_values(array_filter($lines, fn (array $line): bool => $line['text'] !== ''));
    }

    /** @return list<array{title:string,text:string,minutes:int|null,cue:string|null}> */
    private function steps(mixed $value): array
    {
        $steps = [];
        foreach ((array) $value as $step) {
            if (! is_array($step)) {
                continue;
            }
            $title = trim((string) ($step['title'] ?? $step[0] ?? ''));
            $text = trim((string) ($step['text'] ?? $step[1] ?? ''));
            if ($title === '' || $text === '') {
                continue;
            }
            $minutes = $step['minutes'] ?? $step[2] ?? null;
            $cue = $step['cue'] ?? $step[3] ?? null;
            $steps[] = ['title' => $title, 'text' => $text, 'minutes' => is_numeric($minutes) ? (int) $minutes : null, 'cue' => is_string($cue) && trim($cue) !== '' ? trim($cue) : null];
        }

        return $steps;
    }

    /** @return list<string> */
    private function strings(mixed $value): array
    {
        return array_values(array_filter(array_map(fn (mixed $item): string => is_scalar($item) ? trim((string) $item) : '', (array) $value), fn (string $item): bool => $item !== ''));
    }

    private function text(mixed $value): ?string
    {
        return is_string($value) && trim($value) !== '' ? trim($value) : null;
    }

    private function validSlug(mixed $slug): bool
    {
        return is_string($slug) && preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) === 1 && strlen($slug) <= 191;
    }
}
