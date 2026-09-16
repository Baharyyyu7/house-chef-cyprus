<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Services\Seo\SeoMetadataService;
use App\Services\Seo\SeoSchemaService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

final class WeeklyMenuController extends Controller
{
    public function index(Request $request, SeoMetadataService $seo, SeoSchemaService $schema): View
    {
        $currentWeek = (int) Carbon::now()->isoWeek();
        $year = (int) Carbon::now()->year;
        $seed = $year * 100 + $currentWeek;

        // Haftanın başlangıç ve bitiş tarihleri
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        $weekDateRange = $startOfWeek->translatedFormat('d F') . ' - ' . $endOfWeek->translatedFormat('d F Y');

        // Havuzdan haftalık dengeli menü oluştur
        mt_srand($seed);

        $recipes = Recipe::query()
            ->where('status', 'published')
            ->where('verification_status', 'verified')
            ->with(['category', 'cuisine', 'media' => fn ($q) => $q->where('visibility', 'public'), 'recipeIngredients.ingredient'])
            ->get();

        $soups = $recipes->filter(fn ($r) => str_contains($r->category?->slug ?? '', 'corba'))->values();
        $mains = $recipes->filter(fn ($r) => str_contains($r->category?->slug ?? '', 'ana-yemek'))->values();
        $veggies = $recipes->filter(fn ($r) => str_contains($r->category?->slug ?? '', 'zeytinyagli') || str_contains($r->category?->slug ?? '', 'sebze'))->values();
        $salads = $recipes->filter(fn ($r) => str_contains($r->category?->slug ?? '', 'salata') || str_contains($r->category?->slug ?? '', 'meze'))->values();
        $desserts = $recipes->filter(fn ($r) => str_contains($r->category?->slug ?? '', 'tatli'))->values();
        $pastries = $recipes->filter(fn ($r) => str_contains($r->category?->slug ?? '', 'hamur'))->values();

        $days = [
            ['name' => 'Pazartesi', 'tag' => 'Hafif & Dengeli Başlangıç', 'type' => 'light'],
            ['name' => 'Salı', 'tag' => 'Geleneksel Tencere Yemeği', 'type' => 'traditional'],
            ['name' => 'Çarşamba', 'tag' => 'Pratik Mutfak', 'type' => 'practical'],
            ['name' => 'Perşembe', 'tag' => 'Kıbrıs & Akdeniz Esintisi', 'type' => 'mediterranean'],
            ['name' => 'Cuma', 'tag' => 'Hafta Sonu Karşılaması', 'type' => 'fish_or_quick'],
            ['name' => 'Cumartesi', 'tag' => 'Aile Ziyafeti', 'type' => 'feast'],
            ['name' => 'Pazar', 'tag' => 'Pazar Keyfi & Fırın Lezzeti', 'type' => 'sunday'],
        ];

        $weeklyPlan = [];
        $allIngredients = collect();

        foreach ($days as $idx => $day) {
            $dayMenu = [];

            // Çorba (haftada 4 gün)
            if (in_array($idx, [0, 1, 3, 5]) && $soups->isNotEmpty()) {
                $soup = $soups[($idx * 3 + $seed) % $soups->count()];
                $dayMenu['corba'] = $soup;
                $this->collectIngredients($allIngredients, $soup);
            }

            // Ana Yemek (Her gün)
            if ($mains->isNotEmpty()) {
                $main = $mains[($idx * 7 + $seed) % $mains->count()];
                $dayMenu['ana_yemek'] = $main;
                $this->collectIngredients($allIngredients, $main);
            }

            // Sebze / Salata / Meze
            if ($idx % 2 === 0 && $veggies->isNotEmpty()) {
                $veg = $veggies[($idx * 5 + $seed) % $veggies->count()];
                $dayMenu['yan_yemek'] = $veg;
                $this->collectIngredients($allIngredients, $veg);
            } elseif ($salads->isNotEmpty()) {
                $salad = $salads[($idx * 4 + $seed) % $salads->count()];
                $dayMenu['yan_yemek'] = $salad;
                $this->collectIngredients($allIngredients, $salad);
            }

            // Tatlı (Cuma, Cmt, Pazar)
            if (in_array($idx, [4, 5, 6]) && $desserts->isNotEmpty()) {
                $dessert = $desserts[($idx * 2 + $seed) % $desserts->count()];
                $dayMenu['tatli'] = $dessert;
                $this->collectIngredients($allIngredients, $dessert);
            }

            $weeklyPlan[] = [
                'day' => $day['name'],
                'tag' => $day['tag'],
                'menu' => $dayMenu,
            ];
        }

        // Alışveriş listesini tekilleştir ve sırala
        $shoppingList = $allIngredients->unique('name')->values()->groupBy('category');

        $pageTitle = "Haftalık Akdeniz Yemek Menüsü ({$weekDateRange}) · House Chef Cyprus";
        $pageDesc = "Bu hafta ne pişirsem diye düşünmeyin! Pazartesiden pazara 7 günlük dengeli yemek menüsü ve hazır toplu alışveriş listesi.";

        $metadata = [
            'title' => $pageTitle,
            'description' => $pageDesc,
            'canonical' => route('weekly-menu.index'),
            'robots' => 'index,follow',
        ];

        return view('weekly-menu.index', [
            'weeklyPlan' => $weeklyPlan,
            'shoppingList' => $shoppingList,
            'weekDateRange' => $weekDateRange,
            'metadata' => $metadata,
            'seoTitle' => $pageTitle,
            'seoDescription' => $pageDesc,
        ]);
    }

    private function collectIngredients(\Illuminate\Support\Collection $collection, Recipe $recipe): void
    {
        foreach ($recipe->recipeIngredients as $ri) {
            $name = $ri->ingredient?->name ?: $ri->display_text;
            if ($name) {
                $category = $this->guessIngredientCategory($name);
                $collection->push([
                    'name' => mb_convert_case($name, MB_CASE_TITLE, 'UTF-8'),
                    'category' => $category,
                ]);
            }
        }
    }

    private function guessIngredientCategory(string $name): string
    {
        $n = mb_strtolower($name);

        if (str_contains($n, 'et') || str_contains($n, 'kıyma') || str_contains($n, 'tavuk') || str_contains($n, 'kuzu') || str_contains($n, 'dana') || str_contains($n, 'balık')) {
            return 'Kasap & Şarküteri';
        }
        if (str_contains($n, 'soğan') || str_contains($n, 'sarımsak') || str_contains($n, 'domates') || str_contains($n, 'biber') || str_contains($n, 'patates') || str_contains($n, 'havuç') || str_contains($n, 'maydanoz') || str_contains($n, 'limon') || str_contains($n, 'nane') || str_contains($n, 'kabak') || str_contains($n, 'patlıcan')) {
            return 'Manav (Sebze & Yeşillik)';
        }
        if (str_contains($n, 'peynir') || str_contains($n, 'hellim') || str_contains($n, 'süt') || str_contains($n, 'yoğurt') || str_contains($n, 'tereyağı') || str_contains($n, 'yumurta')) {
            return 'Süt & Kahvaltılık';
        }
        if (str_contains($n, 'mercimek') || str_contains($n, 'pirinç') || str_contains($n, 'bulgur') || str_contains($n, 'nohut') || str_contains($n, 'fasulye') || str_contains($n, 'makarna') || str_contains($n, 'un') || str_contains($n, 'şeker')) {
            return 'Kuru Gıda & Bakliyat';
        }

        return 'Baharat & Mutfak Dolabı';
    }
}
