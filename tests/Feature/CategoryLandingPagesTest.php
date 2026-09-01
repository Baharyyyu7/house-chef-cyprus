<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CategoryLandingPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_a_category_with_four_verified_published_recipes_has_a_page_and_sitemap_entry(): void
    {
        $strong = $this->category('Ana Yemek', 'ana-yemek', 4);
        $thin = $this->category('İçecek', 'icecek', 3);

        $this->get(route('categories.show', $strong->slug))->assertOk()->assertSee('Ana Yemek Tarifleri');
        $this->get(route('categories.show', $thin->slug))->assertNotFound();
        $this->get(route('categories.index'))->assertOk()
            ->assertSee(route('categories.show', $strong->slug), false)
            ->assertDontSee(route('categories.show', $thin->slug), false);
        $this->get('/sitemaps/pages.xml')->assertOk()
            ->assertSee(route('categories.show', $strong->slug), false)
            ->assertDontSee(route('categories.show', $thin->slug), false);
    }

    public function test_drafts_do_not_fill_the_category_threshold(): void
    {
        $category = $this->category('Çorba', 'corba', 0);
        $this->recipes($category, 4, 'draft');

        $this->get(route('categories.show', $category->slug))->assertNotFound();
    }

    public function test_category_collection_schema_contains_only_visible_recipe_urls(): void
    {
        $category = $this->category('Tatlı', 'tatli', 4);
        $html = (string) $this->get(route('categories.show', $category->slug))->assertOk()->getContent();

        $this->assertStringContainsString('"@type":"CollectionPage"', $html);
        $this->assertStringContainsString('"numberOfItems":4', $html);
        $this->assertStringNotContainsString('"@type":"Recipe"', $this->collectionSchema($html));
    }

    private function category(string $name, string $slug, int $count): Category
    {
        $category = Category::query()->create(['name' => $name, 'slug' => $slug]);
        $this->recipes($category, $count);

        return $category;
    }

    private function recipes(Category $category, int $count, string $status = 'published'): void
    {
        for ($i = 0; $i < $count; $i++) {
            Recipe::query()->create([
                'title' => $category->name.' '.$i, 'slug' => $category->slug.'-'.$i,
                'status' => $status, 'verification_status' => 'verified',
                'published_at' => now()->subDays($i), 'category_id' => $category->id,
            ]);
        }
    }

    private function collectionSchema(string $html): string
    {
        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $matches);

        return collect($matches[1] ?? [])->first(fn (string $json): bool => str_contains($json, 'CollectionPage'), '');
    }
}
