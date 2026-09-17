<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\Recipe;
use Illuminate\Support\Collection;

/**
 * Yayındaki tariflerde şablon metni ve eksik tarifi ölçer.
 *
 * Neden var: 16 Eylül 2026'da 815 tarif tek günde eklendi. Açıklamaları
 * "X Mutfağı ev mutfağından ilham alan anlaşılır bir tarif" kalıbıyla
 * üretilmişti, bir kısmının hiç adımı yoktu. Aynı gün indeksleme kapısı
 * "onaylı görseli olmalı" kuralından "mutfağı ve kategorisi olsun" kuralına
 * gevşetildi; bu içerik indekse girdi. Site 16 Ağustos 2026'da ölçekli
 * makine içeriği yüzünden bir kez zarar gördü.
 *
 * Ölçüt kasten basit: normalize edilmiş, 60 karakterden uzun cümlenin başka
 * bir yayındaki tarifte birebir geçmesi. Cümlenin sahibi ilk yayınlanan
 * tariftir; tekrar sayılan sonrakilerdir.
 */
final class RecipeQualityGateService
{
    public const ASGARI_CUMLE = 60;

    public const TEKRAR_ESIGI = 0.5;

    public const ASGARI_ADIM = 2;

    public const ASGARI_MALZEME = 3;

    /**
     * @param  Collection<int, Recipe>|null  $tarifler
     * @return array<int, array{units: int, repeated: int, share: float}>
     */
    public function olc(?Collection $tarifler = null): array
    {
        $tarifler ??= Recipe::query()
            ->where('status', 'published')
            ->with('steps:id,recipe_id,instruction')
            ->get(['id', 'title', 'description', 'excerpt', 'published_at']);

        $tarifler = $tarifler
            ->sortBy(static fn (Recipe $t): array => [$t->published_at?->getTimestamp() ?? PHP_INT_MAX, $t->getKey() ?? PHP_INT_MAX])
            ->values();

        $sahip = [];
        $izleri = [];

        foreach ($tarifler as $tarif) {
            $izler = array_values(array_unique($this->parmakIzleri($tarif)));
            $izleri[$tarif->id] = $izler;

            foreach ($izler as $iz) {
                $sahip[$iz] ??= $tarif->id;
            }
        }

        $sonuc = [];

        foreach ($izleri as $id => $izler) {
            $tekrar = count(array_filter($izler, static fn (string $iz): bool => $sahip[$iz] !== $id));
            $sonuc[$id] = [
                'units' => count($izler),
                'repeated' => $tekrar,
                'share' => $izler === [] ? 0.0 : round($tekrar / count($izler), 3),
            ];
        }

        return $sonuc;
    }

    /** @param array{units: int, repeated: int, share: float}|null $olcum */
    public function sablonMu(?array $olcum): bool
    {
        return $olcum !== null && $olcum['units'] > 0 && $olcum['share'] >= self::TEKRAR_ESIGI;
    }

    /** Adımı ya da malzemesi yetersiz tarif tariften sayılmaz. */
    public function eksikMi(Recipe $tarif): bool
    {
        return $tarif->steps()->count() < self::ASGARI_ADIM
            || $tarif->recipeIngredients()->count() < self::ASGARI_MALZEME
            || $this->yerTutucuMalzemeVar($tarif);
    }

    /**
     * "Ana Malzeme" bir malzeme değil, doldurulmamış bir alandır.
     *
     * 17 Eylül 2026'da yayındaki 74 tarifin malzeme listesi bu yer tutucuyla
     * başlıyordu; bir tatlı tarifinin malzemesinde soğan ve sarımsak vardı.
     */
    public function yerTutucuMalzemeVar(Recipe $tarif): bool
    {
        return $tarif->ingredients()
            ->where(fn ($q) => $q->where('slug', 'ana-malzeme')->orWhere('name', 'Ana Malzeme'))
            ->exists();
    }

    /**
     * Açıklama cümleleri ve adım yönergeleri birlikte ölçülür.
     *
     * 111 tarifin ilk adımı birebir "Tüm malzemeleri tarif için hazırlayın ve
     * temizleyin." idi. Açıklamada yemeğin adı geçtiği için açıklama tek
     * başına ayırt edemiyordu; yönergeler ediyor.
     *
     * @return list<string>
     */
    private function parmakIzleri(Recipe $tarif): array
    {
        $metin = trim(strip_tags((string) $tarif->getRawOriginal('description').' '.$tarif->getRawOriginal('excerpt')));
        $izler = [];

        $parcalar = preg_split('/(?<=[.!?])\s+/u', $metin) ?: [];

        if ($tarif->relationLoaded('steps') || $tarif->exists) {
            foreach ($tarif->steps as $adim) {
                $parcalar[] = (string) $adim->getRawOriginal('instruction');
            }
        }

        foreach ($parcalar as $cumle) {
            $sade = mb_strtolower(trim($cumle));
            $sade = preg_replace('/[^\p{L}\p{N} ]+/u', ' ', $sade) ?? $sade;
            $sade = trim(preg_replace('/\s+/u', ' ', $sade) ?? $sade);

            if (mb_strlen($sade) >= self::ASGARI_CUMLE) {
                $izler[] = hash('sha256', $sade);
            }
        }

        return $izler;
    }
}
