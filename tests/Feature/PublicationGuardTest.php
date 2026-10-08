<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Exceptions\PublicationGuardException;
use App\Models\BlogPost;
use App\Models\User;
use App\Services\Seo\BlogIndexability;
use App\Services\Seo\BlogOriginalityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PublicationGuardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['production.editorial.publication_guard' => true, 'production.editorial.daily_publication_cap' => 2]);
    }

    private function yazi(array $alanlar = []): BlogPost
    {
        static $sira = 0;
        $sira++;

        return BlogPost::query()->create($alanlar + [
            'title' => 'Yazı '.$sira,
            'slug' => 'yazi-'.$sira,
            'excerpt' => 'Özet.',
            'body' => 'Gövde.',
            'status' => 'draft',
        ]);
    }

    public function test_a_blog_post_cannot_be_published_without_a_recorded_human_approval(): void
    {
        $yazi = $this->yazi();

        $this->expectException(PublicationGuardException::class);
        $yazi->update(['status' => 'published', 'published_at' => now()]);
    }

    public function test_an_approved_post_publishes_until_the_daily_cap(): void
    {
        $editor = User::factory()->create();
        $onay = ['human_approved_at' => now(), 'human_approved_by_user_id' => $editor->id];

        $this->yazi($onay)->update(['status' => 'published', 'published_at' => now()]);
        $this->yazi($onay)->update(['status' => 'published', 'published_at' => now()]);

        $this->expectException(PublicationGuardException::class);
        $this->yazi($onay)->update(['status' => 'published', 'published_at' => now()]);
    }

    public function test_editing_an_already_published_post_is_not_blocked(): void
    {
        config(['production.editorial.publication_guard' => false]);
        $yazi = $this->yazi(['status' => 'published', 'published_at' => now()]);
        config(['production.editorial.publication_guard' => true]);

        $yazi->update(['title' => 'Yeni başlık']);

        $this->assertSame('Yeni başlık', $yazi->fresh()->title);
    }

    public function test_body_defects_are_detected(): void
    {
        $servis = app(BlogOriginalityService::class);
        $tekrar = str_repeat('Bu konu hakkında daha fazla detaya girmek gerekirse araştırmalar gösteriyor. ', 20);

        $this->assertContains('cumle_tekrari', $servis->govdeKusurlari("## Giriş\n\n".$tekrar));
        $this->assertContains('yer_tutucu', $servis->govdeKusurlari('Bkz. [tarif](/tarifler/ornek-tarif-3).'));
        $this->assertContains('ham_html', $servis->govdeKusurlari('<p>Paragraf</p><h2>Başlık</h2>'));
        $this->assertSame([], $servis->govdeKusurlari("## Hellim\n\nHellim keçi ve koyun sütünden yapılır. Pişirildiğinde erimez, bu yüzden ızgaraya uygundur. Tuzlu suda saklanır ve uzun süre dayanır. Nane ile katlanması adadaki eski bir alışkanlıktır. Köylerde hâlâ kazanda kaynatılır."));
    }

    public function test_an_html_body_is_measured_as_the_reader_sees_it(): void
    {
        $yazi = $this->yazi([
            'body' => '<p>'.implode(' ', array_map(fn (int $i): string => 'Kıbrıs mutfağında zeytinyağı '.$i.'. yemeğin temelidir.', range(1, 80))).'</p>',
            'fact_check_status' => 'passed',
            'originality_status' => 'passed',
        ]);

        $engeller = app(BlogIndexability::class)->engeller($yazi);

        $this->assertContains('ince', $engeller);
        $this->assertContains('ham_html', $engeller);
    }
}
