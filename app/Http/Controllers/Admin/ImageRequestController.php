<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Recipe;
use App\Services\Media\PublicMediaEligibilityService;
use App\Services\Media\ImageDeliveryRequestBuilder;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Görsel üretimi başka bir araçta yapılıyor ve talep bugüne kadar bir betikle
 * masaüstüne yazılan metin dosyasıyla iletiliyordu: hangi tariflerin görseli
 * eksik olduğunu görmek için ayrı bir komut, prompt'u almak için ayrı bir dosya
 * gerekiyordu. Bu ekran ikisini birleştirir -- yemeği seç, talebi kopyala.
 *
 * Liste yalnız adı gerçekten bir yemeği tarif eden tarifleri gösterir; yayındaki
 * 678 tarifin 467'sinin adı "Akdeniz Mutfağı Usulü Limonlu" gibi yarım kalmış
 * kalıplardır ve onlar için üretilecek fotoğrafın göstereceği bir yemek yoktur.
 */
final class ImageRequestController extends Controller
{
    public function index(Request $request, PublicMediaEligibilityService $gate, ImageDeliveryRequestBuilder $builder): View
    {
        $this->authorizeAdminOperations();

        $arama = trim($request->string('q')->toString());

        $tarifler = Recipe::query()
            ->where('status', 'published')
            ->when($arama !== '', fn ($q) => $q->where('title', 'like', '%'.$arama.'%'))
            ->with(['cuisine:id,name', 'category:id,name', 'ingredients:id,name', 'media'])
            ->orderBy('title')
            ->get()
            ->filter(fn (Recipe $recipe): bool => Recipe::titleDescribesADish((string) $recipe->title));

        $eksik = $tarifler
            ->filter(fn (Recipe $recipe): bool => $gate->firstEligible($recipe->media) === null)
            ->map(fn (Recipe $recipe): array => [
                'title' => $recipe->title,
                'slug' => $recipe->slug,
                'cuisine' => $recipe->cuisine?->name,
                'filename' => $builder->filename($recipe),
                'request' => $builder->request($recipe),
            ])
            ->values();

        $hazir = $tarifler->count() - $eksik->count();

        return view('admin.image-requests.index', [
            'eksik' => $eksik,
            'hazir' => $hazir,
            'toplam' => $tarifler->count(),
            'arama' => $arama,
        ]);
    }
}
