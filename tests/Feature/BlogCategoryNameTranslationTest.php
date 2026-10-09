<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogCategory;
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
}
