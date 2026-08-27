<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Recipe;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Sitenin en çok trafik alan sayfasında hiçbir üyelik teklifi yoktu. Davet
 * kartı eklendi; üç kuralı var ve üçü de kolayca sessizce bozulabilir:
 *
 * 1. Ödeme yapmış üyeye satış yapılmaz. Burada "ödeme yapmış", is_premium
 *    bayrağı değil webhook'la doğrulanmış abonelik demektir; isPremium()
 *    bilerek bu kadar katı.
 * 2. Ziyaretçiye ücretsiz hesap, giriş yapmış ücretsiz üyeye premium önerilir.
 * 3. Kartta yalnız kodda karşılığı olan şeyler sayılır ve o karşılık rota
 *    olarak aranır. Bir özelliği İngilizce adıyla arayıp bulamamak, o
 *    özelliğin olmadığı anlamına gelmiyor: menü planlama ve alışveriş
 *    listesi ai-mutfak/menu ve .../alisveris altında gerçekten var.
 */
final class MembershipInviteAdaptsToTheReaderTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_visitor_is_offered_a_free_account(): void
    {
        $this->get($this->tarifAdresi())
            ->assertOk()
            ->assertSee('Bu tarifi kaydedin.')
            ->assertSee('Malzeme dolabı');
    }

    public function test_a_signed_in_free_member_is_offered_premium_not_registration(): void
    {
        $this->actingAs(User::factory()->create())
            ->get($this->tarifAdresi())
            ->assertOk()
            ->assertSee('Reklamsız pişirin.')
            ->assertDontSee('Bu tarifi kaydedin.');
    }

    public function test_a_paying_member_is_never_sold_to(): void
    {
        $uye = User::factory()->create();
        $uye->forceFill([
            'is_premium' => true,
            'premium_plan' => 'monthly',
            'premium_expires_at' => now()->addMonth(),
        ])->save();
        UserSubscription::create([
            'user_id' => $uye->id,
            'plan' => 'monthly',
            'amount' => 4.99,
            'currency' => 'EUR',
            'payment_provider' => 'stripe',
            'provider_subscription_id' => 'sub_invite_hidden',
            'status' => 'active',
            'started_at' => now(),
            'expires_at' => now()->addMonth(),
            'metadata' => ['verified_by_webhook' => true],
        ]);

        $this->actingAs($uye)
            ->get($this->tarifAdresi())
            ->assertOk()
            ->assertDontSee('Reklamsız pişirin.')
            ->assertDontSee('Bu tarifi kaydedin.');
    }

    /**
     * Satılan her premium özelliğin arkasında gerçekten bir sayfa olmalı.
     * Fiyatlandırmadaki liste kolayca gerçekten kopabilir; bu test onu
     * rotalara bağlar.
     */
    public function test_every_premium_feature_we_sell_has_a_page_behind_it(): void
    {
        foreach (['ai-kitchen.meal-plan', 'ai-kitchen.index', 'pantry.index', 'collections.index'] as $rota) {
            $this->assertTrue(
                Route::has($rota),
                "Premium listesinde satılan bir özelliğin rotası yok: {$rota}"
            );
        }
    }

    private function tarifAdresi(): string
    {
        $recipe = Recipe::query()->create([
            'title' => 'Zeytinyağlı Nohutlu Pilav',
            'slug' => 'zeytinyagli-nohutlu-pilav',
            'status' => 'published',
            'verification_status' => 'verified',
            'published_at' => now(),
        ]);

        return route('recipes.show', $recipe);
    }
}
