<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Yayındaki yazıların slug'ları toplu bir işlemle uzatıldığında (sonlarına
 * ayırt edici ek geldiğinde) eski adresler sessizce 404 oldu ve indekslenmiş
 * bağlantılar kırıldı. Eski adres artık kalıcı olarak yenisine yönlenir.
 */
final class BlogSlugRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_old_slug_redirects_permanently_to_the_renamed_post(): void
    {
        $post = $this->publish('kibris-mutfagina-baslangic-rehberi-65PRZ');

        $this->get('/blog/kibris-mutfagina-baslangic-rehberi')
            ->assertRedirect(route('blog.show', $post))
            ->assertStatus(301);
    }

    public function test_the_shortest_matching_slug_wins(): void
    {
        $this->publish('ev-olculeri-rehberi-cok-daha-uzun-bir-varyant-XYZ');
        $dogru = $this->publish('ev-olculeri-rehberi-0JCY7');

        $this->get('/blog/ev-olculeri-rehberi')
            ->assertRedirect(route('blog.show', $dogru));
    }

    public function test_an_ambiguous_prefix_does_not_redirect_to_an_arbitrary_post(): void
    {
        $this->publish('ayni-kok-ABC12');
        $this->publish('ayni-kok-DEF34');

        $this->get('/blog/ayni-kok')->assertNotFound();
    }

    public function test_an_unknown_slug_still_returns_404(): void
    {
        $this->get('/blog/hic-var-olmayan-bir-yazi')->assertNotFound();
    }

    public function test_a_draft_post_does_not_become_a_redirect_target(): void
    {
        BlogPost::query()->create([
            'author_id' => User::factory()->create()->id,
            'title' => 'Taslak',
            'slug' => 'gizli-yazi-ABC',
            'excerpt' => 'Özet.',
            'body' => 'Gövde.',
            'status' => 'draft',
        ]);

        $this->get('/blog/gizli-yazi')->assertNotFound();
    }

    public function test_an_ambiguous_old_slug_stays_404_instead_of_picking_a_post(): void
    {
        $this->publish('mercimek-corbasinda-puf-noktalari-A1B2C');
        $this->publish('mercimek-corbasinda-puf-noktalari-D3E4F');

        $this->get('/blog/mercimek-corbasinda-puf-noktalari')->assertNotFound();
    }

    public function test_an_ambiguous_geo_slug_stays_404_instead_of_picking_a_post(): void
    {
        $this->publish('kibris-zeytinyagli-enginar-A1B2C');
        $this->publish('kibris-zeytinyagli-enginar-D3E4F');

        $this->get('/blog/geo-2026-kibris-zeytinyagli-enginar')->assertNotFound();
    }

    private function publish(string $slug): BlogPost
    {
        return BlogPost::query()->create([
            'author_id' => User::factory()->create()->id,
            'title' => 'Yönlendirme Testi',
            'slug' => $slug,
            'excerpt' => 'Özet.',
            'body' => 'Gövde.',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);
    }
}
