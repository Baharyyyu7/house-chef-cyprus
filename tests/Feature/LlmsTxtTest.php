<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LlmsTxtTest extends TestCase
{
    use RefreshDatabase;

    public function test_llms_txt_returns_ok_with_plain_text(): void
    {
        Recipe::query()->create([
            'title' => 'Kıbrıs Şeftali Kebabı',
            'slug' => 'kibris-seftali-kebabi',
            'status' => 'published',
            'verification_status' => 'verified',
            'prep_minutes' => 20,
            'cook_minutes' => 25,
            'servings' => 4,
            'description' => 'Geleneksel Kıbrıs lezzeti.',
        ]);

        $response = $this->get('/llms.txt');

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('# House Chef Cyprus')
            ->assertSee('Kıbrıs Şeftali Kebabı')
            ->assertSee('/feed')
            ->assertSee('/llms-full.txt');
    }

    public function test_llms_full_txt_returns_ok_with_complete_corpus(): void
    {
        Recipe::query()->create([
            'title' => 'Geleneksel Molehiya',
            'slug' => 'geleneksel-molehiya',
            'status' => 'published',
            'verification_status' => 'verified',
            'prep_minutes' => 30,
            'cook_minutes' => 50,
            'servings' => 6,
            'description' => 'Molehiya otu ve kuzu eti ile.',
        ]);

        $response = $this->get('/llms-full.txt');

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('# House Chef Cyprus - Kapsamlı İçerik Kataloğu')
            ->assertSee('Geleneksel Molehiya');
    }
}
