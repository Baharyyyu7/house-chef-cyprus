<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\BlogPost;
use App\Models\Recipe;

/** Builds schemas from already-public, visible editorial data only. */
final class SeoSchemaService
{
    /** @return array<string,mixed> */
    public function recipe(Recipe $recipe, array $ingredients, string $imageUrl = '', ?string $cuisineUrl = null): array
    {
        $recipeNode = [
            '@type' => 'Recipe', 'name' => $recipe->title,
            'description' => $recipe->description ?: $recipe->excerpt,
            'url' => route('recipes.show', $recipe),
            'mainEntityOfPage' => route('recipes.show', $recipe),
            'dateModified' => $recipe->updated_at?->toAtomString(),
            'recipeCuisine' => $recipe->cuisine?->name,
            'recipeCategory' => $recipe->category?->name,
            'prepTime' => 'PT'.$recipe->prep_minutes.'M', 'cookTime' => 'PT'.$recipe->cook_minutes.'M',
            'totalTime' => 'PT'.$recipe->totalMinutes().'M', 'recipeYield' => $recipe->servings.' porsiyon',
            'recipeIngredient' => $ingredients,
            'recipeInstructions' => $recipe->steps->map(fn ($step): array => ['@type' => 'HowToStep', 'name' => $step->title, 'text' => $step->instruction])->values()->all(),
            // author ve datePublished hiçbir tarifte yoktu. İkisi de Google'ın
            // önerdiği alanlar ve author E-E-A-T tarafında doğrudan sayılıyor -
            // sitenin bütün konumlanması "doğrulanmış tarifler" üzerine ama
            // içeriğin arkasında kimin durduğu söylenmiyordu.
            'author' => [
                '@type' => 'Organization',
                'name' => config('brand.name'),
                'url' => route('home'),
            ],
            'datePublished' => $recipe->published_at?->toAtomString(),
        ];
        if ($imageUrl !== '') {
            $recipeNode['image'] = [$imageUrl];
        }

        $crumbs = [['name' => 'Ana Sayfa', 'url' => route('home')]];
        if ($recipe->cuisine !== null && $cuisineUrl !== null) {
            $crumbs[] = ['name' => 'Mutfaklar', 'url' => route('cuisines.index')];
            $crumbs[] = ['name' => $recipe->cuisine->name, 'url' => $cuisineUrl];
        } else {
            $crumbs[] = ['name' => 'Tarifler', 'url' => route('recipes.index')];
        }
        $crumbs[] = ['name' => $recipe->title, 'url' => route('recipes.show', $recipe)];

        return $this->graph($recipeNode, $crumbs);
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

    /** @param array<string,mixed> $node @param list<array{name:string,url:string}> $crumbs @return array<string,mixed> */
    private function graph(array $node, array $crumbs): array
    {
        return ['@context' => 'https://schema.org', '@graph' => [$node, [
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($crumbs)->values()->map(fn (array $crumb, int $index): array => ['@type' => 'ListItem', 'position' => $index + 1, 'name' => $crumb['name'], 'item' => $crumb['url']])->all(),
        ]]];
    }
}
