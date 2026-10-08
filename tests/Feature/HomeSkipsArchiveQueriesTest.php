<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Ana sayfa arşivle aynı denetleyiciden geçiyor ama arşivin filtre
 * listelerini ve arama sonucunu hiç göstermiyor. Bunlar her ziyarette
 * sorgulanıyordu; yayın kataloğunun tamamı da yalnız sayısı ve ilk dört
 * görselli tarifi için belleğe alınıyordu.
 */
final class HomeSkipsArchiveQueriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_does_not_query_archive_filters_or_the_search(): void
    {
        $this->seed();

        DB::enableQueryLog();
        $this->get(route('home'))->assertOk();
        $sorgular = collect(DB::getQueryLog())->pluck('query')->map(fn (string $q): string => strtolower($q));
        DB::disableQueryLog();

        foreach (['from "allergens"', 'from "dietary_tags" order by', 'from "categories" order by "name"'] as $parca) {
            $this->assertFalse($sorgular->contains(fn (string $q): bool => str_contains($q, $parca)), "Ana sayfa gereksiz sorgu yapıyor: {$parca}");
        }

        // Vitrin kataloğu artık sınırsız okunmuyor: parça parça (limitli) okunur.
        $tarifOkumalari = $sorgular->filter(fn (string $q): bool => str_starts_with($q, 'select "id", "public_id", "cuisine_id"') && str_contains($q, 'from "recipes"'));
        $this->assertNotEmpty($tarifOkumalari);
        $limitsiz = $tarifOkumalari->reject(fn (string $q): bool => str_contains($q, 'limit'));
        $this->assertSame([], $limitsiz->values()->all(), 'Ana sayfa yayın kataloğunun tamamını okuyor.');
    }

    public function test_the_archive_still_lists_filters(): void
    {
        $this->seed();

        $this->get(route('recipes.index'))->assertOk()->assertViewHas('allergens')->assertViewHas('categories')->assertViewHas('dietaryTags');
    }
}
