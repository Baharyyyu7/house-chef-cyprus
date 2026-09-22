<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\SlugRedirect;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Adresi tutan taslak nüsha yönlendirmeyi engellemez.
 *
 * 22 Eylül 2026'da ölçüldü: Google'ın sıraladığı 14 adres 404 veriyordu.
 * Yönlendirme kaydı vardı ama hiç çalışmıyordu; aynı adresi tutan taslak bir
 * nüsha yüzünden rota bağlaması başarılı sayılıyor, sonra yayın denetimi
 * 404'e düşürüyordu. Sinyal eşine taşınmak yerine kayboluyordu.
 */
final class ShadowingDraftDoesNotBlockRedirectTest extends TestCase
{
    use RefreshDatabase;

    private function yazi(string $slug, string $durum): BlogPost
    {
        return BlogPost::query()->create([
            'title' => 'Yazı '.$slug,
            'slug' => $slug,
            'excerpt' => 'Özet.',
            'body' => '<p>Gövde</p>',
            'status' => $durum,
            'published_at' => $durum === 'published' ? now()->subDay() : null,
            'fact_check_status' => 'passed',
            'originality_status' => 'passed',
            'editorial_status' => 'approved',
        ]);
    }

    public function test_taslak_nusha_adresi_tutuyorken_yonlendirme_calisir(): void
    {
        $canli = $this->yazi('kuru-bakliyat-saklama', 'published');
        $taslak = $this->yazi('kuru-bakliyat-saklama-09MMS', 'draft');

        SlugRedirect::query()->create([
            'model_type' => $taslak->getMorphClass(),
            'old_slug' => $taslak->slug,
            'model_id' => $canli->id,
        ]);

        $this->get('/blog/'.$taslak->slug)
            ->assertStatus(301)
            ->assertRedirect(route('blog.show', $canli));
    }

    public function test_yayindaki_sayfa_yonlendirmeyle_ezilmez(): void
    {
        $canli = $this->yazi('yayindaki-yazi', 'published');
        $baska = $this->yazi('baska-yazi', 'published');

        SlugRedirect::query()->create([
            'model_type' => $canli->getMorphClass(),
            'old_slug' => $canli->slug,
            'model_id' => $baska->id,
        ]);

        $this->get('/blog/'.$canli->slug)->assertOk();
    }

    public function test_hedef_de_kapaliysa_yonlendirme_yapilmaz(): void
    {
        $taslak = $this->yazi('taslak-bir', 'draft');
        $hedef = $this->yazi('taslak-iki', 'draft');

        SlugRedirect::query()->create([
            'model_type' => $taslak->getMorphClass(),
            'old_slug' => $taslak->slug,
            'model_id' => $hedef->id,
        ]);

        $this->get('/blog/'.$taslak->slug)->assertNotFound();
    }
}
