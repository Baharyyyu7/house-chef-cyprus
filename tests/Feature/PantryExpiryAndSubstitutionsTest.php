<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\PantryItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class PantryExpiryAndSubstitutionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-09 12:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_pantry_status_uses_the_right_direction_of_days(): void
    {
        // 9 Ekim denetimi: gelecek tarihli ürünler "yakında bitecek", bugün
        // bitenler "süresi dolmuş" sayılıyordu (Carbon diffInDays işareti ters).
        $this->assertSame('fresh', (new PantryItem(['expires_at' => '2026-10-20']))->durum());
        $this->assertSame('soon', (new PantryItem(['expires_at' => '2026-10-12']))->durum());
        $this->assertSame('soon', (new PantryItem(['expires_at' => '2026-10-09']))->durum());
        $this->assertSame('expired', (new PantryItem(['expires_at' => '2026-10-08']))->durum());
        $this->assertSame('undated', (new PantryItem(['expires_at' => null]))->durum());
        $this->assertSame(11, (new PantryItem(['expires_at' => '2026-10-20']))->kalanGun());
    }

    public function test_unchecked_substitutions_box_sends_an_explicit_false(): void
    {
        // Kutu işaretsizken parametre hiç gelmiyordu; varsayılan true kutuyu geri açıyordu.
        $this->get(route('ingredients.explore'))
            ->assertOk()
            ->assertSee('<input type="hidden" name="include_substitutions" value="0">', false);
    }
}
