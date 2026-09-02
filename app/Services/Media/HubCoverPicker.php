<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Models\Media;
use App\Models\Recipe;
use Illuminate\Support\Facades\DB;

/**
 * Hub kartları için temsilci yemek görseli seçer.
 *
 * Mutfak, kategori ve malzeme sayfalarının tamamı metin kartlarından
 * oluşuyordu: bir yemek sitesinde, ürünün kendisi olan fotoğraf hiçbir
 * koleksiyon sayfasında görünmüyordu.
 *
 * Yeni görsel üretilmiyor; var olan onaylı görsellerden seçiliyor. Seçim
 * aynı kamusal medya kapısından geçiyor - kartta gösterilen görsel, tarif
 * sayfasında gösterilebilecek görselle aynı kurala tabi.
 *
 * Görseli olmayan hub için `null` dönüyor ve kart metin hâlinde kalıyor.
 * Yer tutucu basmak, boş bırakmaktan kötü.
 */
final class HubCoverPicker
{
    public function __construct(private readonly PublicMediaEligibilityService $kapi) {}

    /**
     * Tarif tablosundaki bir sütuna göre sahip başına temsilci görsel.
     *
     * @param  list<int>  $sahipIdleri
     * @return array<int, Media>
     */
    public function sutunaGore(string $sutun, array $sahipIdleri): array
    {
        if ($sahipIdleri === []) {
            return [];
        }

        $tarifler = Recipe::query()
            ->whereIn($sutun, $sahipIdleri)
            ->where('status', 'published')
            ->where('verification_status', 'verified')
            ->with(['media' => fn ($q) => $q->where('visibility', 'public')])
            ->orderByDesc('published_at')
            ->get();

        return $this->ilkUygunlar($tarifler, fn (Recipe $t): ?int => $t->{$sutun} === null ? null : (int) $t->{$sutun});
    }

    /**
     * Malzeme başına temsilci görsel. İlişki ara tablo üzerinden.
     *
     * @param  list<int>  $malzemeIdleri
     * @return array<int, Media>
     */
    public function malzemeyeGore(array $malzemeIdleri): array
    {
        if ($malzemeIdleri === []) {
            return [];
        }

        $baglar = DB::table('recipe_ingredients')
            ->whereIn('ingredient_id', $malzemeIdleri)
            ->select('ingredient_id', 'recipe_id')
            ->get();

        $tarifler = Recipe::query()
            ->whereIn('id', $baglar->pluck('recipe_id')->unique()->all())
            ->where('status', 'published')
            ->where('verification_status', 'verified')
            ->with(['media' => fn ($q) => $q->where('visibility', 'public')])
            ->orderByDesc('published_at')
            ->get()
            ->keyBy('id');

        $secilen = [];

        foreach ($baglar as $bag) {
            $malzeme = (int) $bag->ingredient_id;

            if (isset($secilen[$malzeme])) {
                continue;
            }

            $gorsel = $this->kapi->firstEligible($tarifler->get($bag->recipe_id)?->media ?? []);

            if ($gorsel instanceof Media) {
                $secilen[$malzeme] = $gorsel;
            }
        }

        return $secilen;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Recipe>  $tarifler
     * @param  callable(Recipe): ?int  $sahip
     * @return array<int, Media>
     */
    private function ilkUygunlar($tarifler, callable $sahip): array
    {
        $secilen = [];

        foreach ($tarifler as $tarif) {
            $id = $sahip($tarif);

            if ($id === null || isset($secilen[$id])) {
                continue;
            }

            $gorsel = $this->kapi->firstEligible($tarif->media);

            if ($gorsel instanceof Media) {
                $secilen[$id] = $gorsel;
            }
        }

        return $secilen;
    }
}
