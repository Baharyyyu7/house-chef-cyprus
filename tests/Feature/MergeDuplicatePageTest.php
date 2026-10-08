<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class MergeDuplicatePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_weaker_twin_is_drafted_and_redirects_to_the_stronger(): void
    {
        $yazi = fn (string $slug): BlogPost => BlogPost::query()->create(['title' => 'Ev ölçüleri', 'slug' => $slug, 'excerpt' => 'Ö', 'body' => 'Gövde.', 'status' => 'published', 'published_at' => now()->subDay()]);
        $guclu = $yazi('ev-olculeri-rehberi');
        $zayif = $yazi('ev-olculeri-rehberi-su-bardagi');

        $this->artisan('icerik:ikizi-birlestir blog ev-olculeri-rehberi-su-bardagi ev-olculeri-rehberi --apply')->assertSuccessful();

        $this->assertSame('draft', $zayif->fresh()->status);
        $this->get('/blog/ev-olculeri-rehberi-su-bardagi')->assertRedirect(route('blog.show', $guclu))->assertStatus(301);
    }
}
