<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class BlogCategoryNameTranslationTest extends TestCase
{
    use RefreshDatabase;

    public function test_blog_category_name_follows_the_page_language(): void
    {
        // Blog kategori adları EN sayfada Türkçe kalıyordu; çeviri, kategori
        // adlarının da taxonomy anahtarlarından okunmasıyla geliyor.
        $kategori = BlogCategory::query()->create([
            'public_id' => (string) Str::ulid(),
            'name' => 'Kıbrıs Mutfağı',
            'slug' => 'kibris-mutfagi',
            'description' => 'Kıbrıs sofrası ve ev mutfağı notları.',
        ]);

        app()->setLocale('en');
        $this->assertSame('Cypriot Cuisine', taxonomy_name($kategori));

        app()->setLocale('tr');
        $this->assertSame('Kıbrıs Mutfağı', taxonomy_name($kategori));
    }

    public function test_a_blog_category_without_a_translation_keeps_its_stored_name(): void
    {
        $kategori = BlogCategory::query()->create([
            'public_id' => (string) Str::ulid(),
            'name' => 'Yeni Kategori',
            'slug' => 'yeni-kategori-'.uniqid(),
            'description' => 'Çevirisi henüz girilmedi.',
        ]);

        app()->setLocale('en');
        $this->assertSame('Yeni Kategori', taxonomy_name($kategori));
        app()->setLocale('tr');
    }

    /**
     * 12 Ekim 2026'da canlıda açık de/ru/pt/pl/fa yazıları kategoriyi "Mutfak
     * Rehberi" diye basıyordu, articleSection dahil: anahtarlar yalnız EN'e
     * ve yayında olmayan kategorilere girmişti.
     */
    public function test_foreign_blog_page_and_card_print_the_category_in_their_language(): void
    {
        $kategori = BlogCategory::query()->create([
            'public_id' => (string) Str::ulid(),
            'name' => 'Mutfak Rehberi',
            'slug' => 'mutfak-rehberi',
            'description' => 'Mutfak notları.',
        ]);
        $yazi = BlogPost::query()->create([
            'title' => 'Hellim rehberi', 'slug' => 'hellim-rehberi', 'excerpt' => 'Özet.', 'body' => 'Gövde metni.',
            'status' => 'published', 'published_at' => '2026-10-05 10:00:00', 'blog_category_id' => $kategori->id,
        ]);
        foreach (['title' => 'Halloumi-Leitfaden', 'excerpt' => 'Zusammenfassung.', 'body' => 'Textkörper.'] as $alan => $deger) {
            $yazi->translations()->create(['locale' => 'de', 'field' => $alan, 'value' => $deger]);
        }

        $this->get('/de/blog/hellim-rehberi')->assertOk()->assertSee('Küchenratgeber')->assertSee('"articleSection":"Küchenratgeber"', false)->assertDontSee('Mutfak Rehberi');
        $this->get('/de/blog')->assertOk()->assertSee('Küchenratgeber');
        $this->get('/blog/hellim-rehberi')->assertOk()->assertSee('Mutfak Rehberi');
    }

    public function test_every_foreign_locale_names_the_same_blog_categories(): void
    {
        $anahtarlar = fn (string $dil): array => array_values(array_filter(
            array_keys(json_decode((string) file_get_contents(lang_path($dil.'.json')), true)),
            fn (string $k): bool => str_starts_with($k, 'taxonomy.blog_category.'),
        ));
        $en = $anahtarlar('en');
        sort($en);

        foreach (['de', 'fa', 'pl', 'pt', 'ru'] as $dil) {
            $bu = $anahtarlar($dil);
            sort($bu);
            $this->assertSame($en, $bu, $dil);
        }
    }
}
