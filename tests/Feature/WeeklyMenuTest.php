<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class WeeklyMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_weekly_menu_page_returns_successful_response(): void
    {
        $corba = Category::create([
            'name' => 'Çorba',
            'slug' => 'corba',
            'public_id' => '01TESTCORBA000000000000001',
        ]);

        $anaYemek = Category::create([
            'name' => 'Ana Yemek',
            'slug' => 'ana-yemek',
            'public_id' => '01TESTANAYEMEK000000000001',
        ]);

        Recipe::create([
            'title' => 'Süzme Mercimek Çorbası',
            'slug' => 'suzme-mercimek-corbasi',
            'description' => 'Geleneksel mercimek çorbası tarifi.',
            'category_id' => $corba->id,
            'status' => 'published',
            'public_id' => '01TESTRECIPECORBA000000001',
        ]);

        Recipe::create([
            'title' => 'Fırında Kuzu İncik',
            'slug' => 'firinda-kuzu-incik',
            'description' => 'Lokum gibi pişen kuzu incik tarifi.',
            'category_id' => $anaYemek->id,
            'status' => 'published',
            'public_id' => '01TESTRECIPEINCIK000000001',
        ]);

        $response = $this->get(route('weekly-menu.index'));

        $response->assertOk();
        $response->assertSee('Haftalık Akdeniz Yemek Menüsü');
        $response->assertSee('Pazartesi');
        $response->assertSee('Toplu Alışveriş Listesi');
        $response->assertSee('WhatsApp');
    }
}
