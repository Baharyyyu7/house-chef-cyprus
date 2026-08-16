<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * AI Mutfak dört eşit ağırlıkta kutudan oluşuyordu ve kullanıcı nereden
 * başlayacağını bilmiyordu. Sayfanın tek bir birincil eylemi olmalı, yardımcı
 * akışlar ne işe yaradığını söylemeli ve gönderim sırasında geri bildirim vermeli.
 */
final class AiKitchenWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_page_leads_with_a_single_primary_action(): void
    {
        $this->actingAsMember()
            ->get(route('ai-kitchen.index'))
            ->assertOk()
            ->assertSee('hc-ai-primary', false)
            ->assertSee('Sorunuzu yazın', false)
            ->assertSee('Öneri al', false);
    }

    public function test_each_secondary_flow_explains_itself(): void
    {
        $response = $this->actingAsMember()->get(route('ai-kitchen.index'))->assertOk();

        $response->assertSee('Dolabımdakilerle', false)
            ->assertSee('süresi geçmemiş ürünlere göre öneri üretir', false)
            ->assertSee('Artanları değerlendir', false)
            ->assertSee('israf etmeden', false)
            ->assertSee('Haftalık menü taslağı', false);
    }

    public function test_submissions_report_progress_instead_of_looking_frozen(): void
    {
        // AI çağrıları saniyeler sürüyor; buton durumu değişmezse kullanıcı tekrar tıklıyor.
        $this->actingAsMember()
            ->get(route('ai-kitchen.index'))
            ->assertOk()
            ->assertSee('Hazırlanıyor…', false)
            ->assertSee('x-bind:disabled="busy"', false);
    }

    public function test_leftover_rows_can_grow_beyond_the_two_fixed_inputs(): void
    {
        $this->actingAsMember()
            ->get(route('ai-kitchen.index'))
            ->assertOk()
            ->assertSee('+ Malzeme ekle', false);
    }

    public function test_the_workspace_is_never_indexed(): void
    {
        $this->actingAsMember()
            ->get(route('ai-kitchen.index'))
            ->assertOk()
            ->assertSee('name="robots" content="noindex,nofollow"', false);
    }

    private function actingAsMember(): self
    {
        return $this->actingAs(User::factory()->create(['email_verified_at' => now()]));
    }
}
