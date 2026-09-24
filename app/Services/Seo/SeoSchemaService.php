<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\BlogPost;
use App\Models\BlogPostTranslation;
use App\Models\Recipe;
use App\Models\RecipeTranslation;

/** Builds schemas from already-public, visible editorial data only. */
final class SeoSchemaService
{
    /** @return array<string,mixed> */
    public function recipe(Recipe $recipe, array $ingredients, string $imageUrl = ''): array
    {
        $recipeNode = [
            '@type' => 'Recipe', 'name' => $recipe->title,
            'description' => $recipe->excerpt ?: $recipe->description,
            'url' => route('recipes.show', $recipe),
            'mainEntityOfPage' => route('recipes.show', $recipe),
            'dateModified' => $recipe->updated_at?->toAtomString(),
            'recipeCuisine' => $recipe->cuisine?->name,
            'recipeCategory' => $recipe->category?->name,
            'prepTime' => 'PT'.$recipe->prep_minutes.'M', 'cookTime' => 'PT'.$recipe->cook_minutes.'M',
            'totalTime' => 'PT'.$recipe->totalMinutes().'M', 'recipeYield' => $recipe->servings.' porsiyon',
            'recipeIngredient' => $ingredients,
            'recipeInstructions' => $recipe->steps->map(fn ($step): array => ['@type' => 'HowToStep', 'name' => $step->title, 'text' => $step->instruction])->values()->all(),
        ];
        if ($imageUrl !== '') {
            $recipeNode['image'] = [$imageUrl];
        }

        return $this->graph($recipeNode, [
            ['name' => 'Ana Sayfa', 'url' => route('home')],
            ['name' => 'Tarifler', 'url' => route('recipes.index')],
            ['name' => $recipe->title, 'url' => route('recipes.show', $recipe)],
        ]);
    }

    /** @return array<string,mixed> */
    public function article(BlogPost $post, string $imageUrl = ''): array
    {
        $publisher = [
            '@type' => 'Organization',
            'name' => config('brand.name'),
            'logo' => url(config('brand.assets.mark_light')),
        ];

        $article = ['@type' => 'BlogPosting', 'headline' => $post->title, 'description' => $post->excerpt, 'datePublished' => $post->published_at?->toAtomString(), 'dateModified' => $post->updated_at?->toAtomString(), 'mainEntityOfPage' => route('blog.show', $post), 'author' => $publisher, 'publisher' => $publisher, 'inLanguage' => 'tr'];
        if ($imageUrl !== '') {
            $article['image'] = [$imageUrl];
        }
        $graph = $this->graph($article, [
            ['name' => 'Ana Sayfa', 'url' => route('home')],
            ['name' => 'Blog', 'url' => route('blog.index')],
            ['name' => $post->title, 'url' => route('blog.show', $post)],
        ]);
        $faqBlocks = $post->getAttribute('faq_blocks');
        $questions = collect(is_array($faqBlocks) ? $faqBlocks : [])->filter(fn (mixed $faq): bool => is_array($faq) && filled($faq['question'] ?? null) && filled($faq['answer'] ?? null))->map(fn (array $faq): array => ['@type' => 'Question', 'name' => $faq['question'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['answer']]])->values()->all();
        if ($questions !== []) {
            $graph['@graph'][] = ['@type' => 'FAQPage', 'mainEntity' => $questions];
        }

        return $graph;
    }

    /** @return array<string,mixed> */
    public function localizedRecipe(RecipeTranslation $translation, string $imageUrl = ''): array
    {
        $recipe = $translation->recipe;
        $url = route($translation->locale.'.recipes.show', $translation->slug);
        $publisher = ['@type' => 'Organization', 'name' => config('brand.name'), 'url' => url('/'), 'logo' => url(config('brand.assets.mark_light'))];
        $recipeNode = [
            '@type' => 'Recipe', 'name' => $translation->title,
            'description' => $translation->excerpt ?: $translation->description,
            'url' => $url, 'mainEntityOfPage' => $url, 'inLanguage' => $translation->locale,
            'author' => $publisher, 'publisher' => $publisher,
            'datePublished' => $recipe?->published_at?->toAtomString(),
            'dateModified' => $translation->updated_at?->toAtomString(),
            'recipeCuisine' => $translation->cuisine_label,
            'recipeCategory' => $translation->category_label,
            'keywords' => implode(', ', (array) $translation->keywords),
            'prepTime' => 'PT'.$translation->prepMinutes().'M', 'cookTime' => 'PT'.$translation->cookMinutes().'M',
            'totalTime' => 'PT'.$translation->totalMinutes().'M',
            'recipeYield' => $translation->servingCount().' '.($translation->servingCount() === 1 ? 'serving' : 'servings'),
            'recipeIngredient' => collect((array) $translation->ingredients)->pluck('text')->filter()->values()->all(),
            'recipeInstructions' => collect((array) $translation->steps)->values()->map(fn (array $step, int $index): array => ['@type' => 'HowToStep', 'position' => $index + 1, 'name' => $step['title'] ?? '', 'text' => $step['text'] ?? '', 'url' => $url.'#step-'.($index + 1)])->all(),
        ];
        if ($imageUrl !== '') {
            $recipeNode['image'] = [$imageUrl];
        }

        return $this->graph(array_filter($recipeNode, fn (mixed $value): bool => $value !== null && $value !== '' && $value !== []), [
            ['name' => 'Home', 'url' => route($translation->locale.'.home')],
            ['name' => 'Recipes', 'url' => route($translation->locale.'.recipes.index')],
            ['name' => $translation->title, 'url' => $url],
        ]);
    }

    /** @return array<string,mixed> */
    public function localizedArticle(BlogPostTranslation $translation, string $imageUrl = ''): array
    {
        $post = $translation->post;
        $url = route($translation->locale.'.blog.show', $translation->slug);
        $publisher = ['@type' => 'Organization', 'name' => config('brand.name'), 'url' => url('/'), 'logo' => url(config('brand.assets.mark_light'))];
        $article = ['@type' => 'BlogPosting', 'headline' => $translation->title, 'description' => $translation->excerpt, 'datePublished' => $post?->published_at?->toAtomString(), 'dateModified' => $translation->updated_at?->toAtomString(), 'mainEntityOfPage' => $url, 'author' => $publisher, 'publisher' => $publisher, 'inLanguage' => $translation->locale, 'keywords' => implode(', ', (array) $translation->keywords)];
        if ($imageUrl !== '') {
            $article['image'] = [$imageUrl];
        }
        $graph = $this->graph(array_filter($article, fn (mixed $value): bool => $value !== null && $value !== ''), [
            ['name' => 'Home', 'url' => route($translation->locale.'.home')],
            ['name' => 'Blog', 'url' => route($translation->locale.'.blog.index')],
            ['name' => $translation->title, 'url' => $url],
        ]);
        $questions = collect((array) $translation->faq_blocks)->filter(fn (mixed $faq): bool => is_array($faq) && filled($faq['question'] ?? null) && filled($faq['answer'] ?? null))->map(fn (array $faq): array => ['@type' => 'Question', 'name' => $faq['question'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['answer']]])->values()->all();
        if ($questions !== []) {
            $graph['@graph'][] = ['@type' => 'FAQPage', 'mainEntity' => $questions];
        }

        return $graph;
    }

    /** @param array<string,mixed> $node @param list<array{name:string,url:string}> $crumbs @return array<string,mixed> */
    private function graph(array $node, array $crumbs): array
    {
        return ['@context' => 'https://schema.org', '@graph' => [$node, [
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($crumbs)->values()->map(fn (array $crumb, int $index): array => ['@type' => 'ListItem', 'position' => $index + 1, 'name' => $crumb['name'], 'item' => $crumb['url']])->all(),
        ]]];
    }
}
