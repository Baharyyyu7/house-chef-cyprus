<?php

namespace Tests\Feature;

use App\Models\AffiliateLink;
use App\Models\DigitalProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonetizationAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_integrity_audit_passes_for_an_empty_safe_database(): void
    {
        $this->artisan('monetization:audit --json')
            ->expectsOutputToContain('"valid":true')
            ->assertSuccessful();
    }

    public function test_launch_audit_fails_closed_when_payments_are_disabled(): void
    {
        config()->set('monetization.payments_enabled', false);

        $this->artisan('monetization:audit --launch --json')
            ->expectsOutputToContain('"stripe_checkout_ready":false')
            ->assertFailed();
    }

    public function test_audit_detects_undeliverable_products_fake_sales_and_unsafe_affiliates(): void
    {
        DigitalProduct::query()->create([
            'title' => 'Hazır Olmayan Ürün',
            'slug' => 'hazir-olmayan-urun',
            'description' => 'Dosyası yok',
            'price' => 10,
            'currency' => 'EUR',
            'file_path' => null,
            'sales_count' => 42,
            'is_active' => true,
        ]);
        AffiliateLink::query()->create([
            'title' => 'İzinsiz hedef',
            'slug' => 'izinsiz-hedef',
            'target_url' => 'http://unsafe.example/product',
            'is_active' => true,
        ]);

        $this->artisan('monetization:audit --json')
            ->expectsOutputToContain('"valid":false')
            ->assertFailed();
    }
}
