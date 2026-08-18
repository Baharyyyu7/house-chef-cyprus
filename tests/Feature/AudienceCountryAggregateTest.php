<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AudienceCountryDay;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AudienceCountryAggregateTest extends TestCase
{
    use RefreshDatabase;

    public function test_visit_with_a_geo_header_increments_only_a_country_counter(): void
    {
        $this->withHeader('CF-IPCountry', 'tr')->get('/')->assertOk();

        $satir = AudienceCountryDay::query()->sole();

        $this->assertSame('TR', $satir->country_code);
        $this->assertSame(1, $satir->hits);
        // Sayaçtan başka bir şey tutulmuyor: kişiye bağlanabilecek bir sütun
        // varsa bu iddia kırılır ve o sütunun neden eklendiği sorulur.
        $this->assertSame(
            ['id', 'day', 'country_code', 'hits', 'created_at', 'updated_at'],
            array_keys($satir->getAttributes())
        );
    }

    public function test_second_visit_from_the_same_country_adds_to_the_same_row(): void
    {
        $this->withHeader('CF-IPCountry', 'DE')->get('/')->assertOk();
        $this->withHeader('CF-IPCountry', 'DE')->get('/')->assertOk();

        $this->assertSame(1, AudienceCountryDay::query()->count());
        $this->assertSame(2, (int) AudienceCountryDay::query()->sole()->hits);
    }

    public function test_a_visit_without_a_geo_header_records_nothing(): void
    {
        $this->get('/')->assertOk();

        $this->assertSame(0, AudienceCountryDay::query()->count());
    }

    public function test_a_malformed_country_header_is_ignored(): void
    {
        $this->withHeader('CF-IPCountry', 'XX')->get('/')->assertOk();
        $this->withHeader('CF-IPCountry', 'bilinmiyor')->get('/')->assertOk();

        $this->assertSame(0, AudienceCountryDay::query()->count());
    }

    public function test_dashboard_map_prefers_visitor_countries_over_member_profiles(): void
    {
        AudienceCountryDay::query()->create(['day' => now()->toDateString(), 'country_code' => 'DE', 'hits' => 12]);

        $yonetici = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);

        $this->actingAs($yonetici)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Almanya');
    }
}
