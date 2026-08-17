<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class HomeTestimonialsTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_shows_turkish_named_testimonial_placeholders_without_photos(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Kullanıcılarımız ne diyor?')
            ->assertSee('Elif Karaca')
            ->assertSee('Mert Yalçın')
            ->assertSee('Selin Arslan')
            ->assertSee('temsili yerleşim içeriğidir')
            ->assertSee('Yorum akışını duraklat', false)
            ->assertDontSee('data-testimonial-avatar', false);
    }

    public function test_every_public_testimonial_is_disclosed_and_has_a_full_turkish_name_without_an_image(): void
    {
        $items = collect(config('testimonials.home'));

        $this->assertCount(9, $items);
        $this->assertNotEmpty(config('testimonials.disclosure'));

        $items->each(function (array $item): void {
            $this->assertMatchesRegularExpression('/^\S+\s+\S+$/u', $item['name']);
            $this->assertNotEmpty($item['role']);
            $this->assertNotEmpty($item['text']);
            $this->assertNull($item['image']);
        });
    }
}
