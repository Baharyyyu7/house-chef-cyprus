<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AudienceCountryDay;
use App\Services\Geo\AudienceCountryBuffer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class AudienceCountryIsBufferedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Üretim davranışı: tampon en fazla dakikada bir aktarılır.
        config()->set('production.audience_flush_seconds', 60);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_visits_within_a_minute_cost_no_table_write_until_the_flush(): void
    {
        // İlk isabet aktarım penceresini açar ve hemen yazılır.
        $this->withHeader('CF-IPCountry', 'DE')->get('/')->assertOk();
        $this->assertSame(1, (int) AudienceCountryDay::query()->sole()->hits);

        DB::enableQueryLog();
        $this->withHeader('CF-IPCountry', 'DE')->get('/')->assertOk();
        $this->withHeader('CF-IPCountry', 'TR')->get('/')->assertOk();
        $yazmalar = collect(DB::getQueryLog())->filter(fn (array $q): bool => str_contains($q['query'], 'audience_country_days'));
        DB::disableQueryLog();

        $this->assertCount(0, $yazmalar, 'Pencere içindeki isabet tabloya dokunmamalı.');
        $this->assertSame(1, (int) AudienceCountryDay::query()->sole()->hits);

        // Dakikalık görev birikeni ülke başına tek yazmayla aktarır.
        $this->assertSame(2, app(AudienceCountryBuffer::class)->flush());
        $this->assertSame(2, (int) AudienceCountryDay::query()->where('country_code', 'DE')->value('hits'));
        $this->assertSame(1, (int) AudienceCountryDay::query()->where('country_code', 'TR')->value('hits'));

        // Aktarılan sayı tampondan düşülür; ikinci aktarım çift saymaz.
        $this->assertSame(0, app(AudienceCountryBuffer::class)->flush());
        $this->assertSame(2, (int) AudienceCountryDay::query()->where('country_code', 'DE')->value('hits'));
    }

    public function test_hits_buffered_before_midnight_land_on_that_day(): void
    {
        Carbon::setTestNow('2026-10-08 23:59:30');
        app(AudienceCountryBuffer::class)->record('CY');
        app(AudienceCountryBuffer::class)->record('CY');

        Carbon::setTestNow('2026-10-09 00:00:30');
        app(AudienceCountryBuffer::class)->flush();

        $this->assertSame(2, (int) AudienceCountryDay::query()->where('day', '2026-10-08')->where('country_code', 'CY')->value('hits'));
    }

    public function test_the_flush_is_scheduled_every_minute(): void
    {
        $gorev = collect(app(\Illuminate\Console\Scheduling\Schedule::class)->events())->first(fn ($e): bool => $e->description === 'audience_country_flush');

        $this->assertNotNull($gorev);
        $this->assertSame('* * * * *', $gorev->expression);
    }
}
