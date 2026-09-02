<?php

declare(strict_types=1);

namespace App\Services\Integrity;

use App\Models\Recipe;
use Illuminate\Support\Collection;

/**
 * Görseli olmayan yayındaki tarifleri önceliklendirir.
 *
 * Yayındaki 129 tarifin 59'u onaylı görseli olmadığı için yer tutucuyla
 * gösteriliyor. Bu, ziyaretçinin gördüğü en büyük eksiklik ve görsel
 * üretimi bu depoda başka ajanlara (Codex, Antigravity) veriliyor.
 *
 * Rapor onlara doğrudan verilebilecek bir sıra üretiyor. Sıralama tahmine
 * değil sitenin kendi verisine dayanıyor: bir tarif ne kadar çok yerden
 * bağlanıyorsa ve arama tarafında ne kadar karşılığı varsa o kadar önce
 * gelir. Rastgele 59 görsel üretmek yerine önce görüneni düzeltmek.
 */
final class MediaGapReport
{
    /** @return array<string, mixed> */
    public function build(int $limit = 25): array
    {
        $eksikler = $this->gorselsizTarifler();

        $siralı = $eksikler
            ->map(fn (Recipe $tarif): array => [
                'slug' => $tarif->slug,
                'title' => $tarif->title,
                'cuisine' => $tarif->cuisine?->name,
                'category' => $tarif->category?->name,
                'score' => $this->oncelik($tarif),
                'reason' => $this->gerekce($tarif),
            ])
            ->sortByDesc('score')
            ->values();

        $onay = $this->onayBekleyen();

        return [
            'status' => $eksikler->isEmpty() ? 'complete' : 'gaps_found',
            'checked_at' => now()->toIso8601String(),
            'published_recipes' => Recipe::query()->where('status', 'published')->where('verification_status', 'verified')->count(),
            'missing_media' => $eksikler->count(),
            // Bitmiş ama görünmeyen görseller. Üretim kapısı insan onayı
            // istiyor ve istemekte haklı; ama onay verilmediği sürece
            // tamamlanan görsel hiçbir yerde görünmüyordu - ne sitede ne
            // panelde. 26 tamamlanmış görsel böyle bekliyordu.
            'awaiting_approval' => $onay['actionable'],
            // Üzerine gidilemeyenler ayrı: sayıya karışırsa iş listesi
            // yanıltıcı olur.
            'awaiting_approval_duplicate' => $onay['duplicate'],
            'awaiting_approval_orphaned' => $onay['orphaned'],
            'queue' => $siralı->take(max(1, $limit))->all(),
        ];
    }

    /**
     * İnsan onayı bekleyenlerin üzerine gidilebilir olanları.
     *
     * Ham sayı yanıltıcıydı: 23 bekleyenin 6'sı silinmiş içeriğe ait, 5'i
     * aynı tarifin ikinci kopyasıydı. "23 görsel onay bekliyor" diyen bir
     * gösterge, yarısı üzerine gidilemeyen bir iş listesi sunar ve toplu
     * onay verildiğinde silinmiş içeriğe ve mükerrer görsele onay verilir.
     *
     * @return array{actionable:int, duplicate:int, orphaned:int}
     */
    private function onayBekleyen(): array
    {
        $kapi = app(\App\Services\Media\PublicMediaEligibilityService::class);
        $bekleyenler = \App\Models\Media::query()
            ->where('media_status', 'approved_real_media')
            ->get()
            ->filter(fn ($medya): bool => $kapi->reason($medya) === 'human_approval_missing')
            ->sortBy('id');

        $uzerineGidilebilir = 0;
        $mukerrer = 0;
        $sahipsiz = 0;
        $gorulen = [];

        foreach ($bekleyenler as $medya) {
            // Silinmiş ya da hiç bulunmayan içeriğin görselini onaylamak
            // hiçbir şey yayınlamaz.
            if (! $this->sahibiDuruyor($medya)) {
                $sahipsiz++;

                continue;
            }

            $anahtar = $medya->mediable_type.':'.$medya->mediable_id;

            if (isset($gorulen[$anahtar])) {
                $mukerrer++;

                continue;
            }

            $gorulen[$anahtar] = true;
            $uzerineGidilebilir++;
        }

        return ['actionable' => $uzerineGidilebilir, 'duplicate' => $mukerrer, 'orphaned' => $sahipsiz];
    }

    private function sahibiDuruyor(\App\Models\Media $medya): bool
    {
        $sahip = $medya->mediable_type === Recipe::class
            ? Recipe::withTrashed()->find($medya->mediable_id)
            : $medya->mediable;

        if ($sahip === null) {
            return false;
        }

        return ! (method_exists($sahip, 'trashed') && $sahip->trashed());
    }

    /** @return Collection<int, Recipe> */
    private function gorselsizTarifler(): Collection
    {
        return Recipe::query()
            ->where('status', 'published')
            ->where('verification_status', 'verified')
            ->whereDoesntHave('media', fn ($query) => $query->where('media_status', 'approved_real_media'))
            ->with(['cuisine', 'category', 'recipeIngredients'])
            ->get();
    }

    /**
     * Öncelik puanı.
     *
     * Ağırlıklar editoryal bir karar, algoritmadan çıkmıyor: Kıbrıs mutfağı
     * bu sitenin ayırt edici tarafı ve o sorgularda gerçek talep ölçüldü
     * ("kıbrıs mutfağı" 21 arama). Öne çıkarılan tarif zaten ana sayfada
     * görünüyor, oradaki yer tutucu en çok göze batan. Malzemesi zengin
     * tarif fotoğrafı çekilebilir bir tarif demek.
     */
    private function oncelik(Recipe $tarif): int
    {
        $puan = 0;

        if ($tarif->is_featured) {
            $puan += 40;
        }

        if (str_contains(mb_strtolower((string) $tarif->cuisine?->name, 'UTF-8'), 'kıbrıs')) {
            $puan += 30;
        }

        $malzeme = $tarif->recipeIngredients->count();
        $puan += min(20, $malzeme * 2);

        if (mb_strlen(trim((string) $tarif->description)) >= 120) {
            $puan += 10;
        }

        return $puan;
    }

    private function gerekce(Recipe $tarif): string
    {
        $sebepler = [];

        if ($tarif->is_featured) {
            $sebepler[] = 'öne çıkarılmış';
        }

        if (str_contains(mb_strtolower((string) $tarif->cuisine?->name, 'UTF-8'), 'kıbrıs')) {
            $sebepler[] = 'Kıbrıs mutfağı';
        }

        if ($tarif->recipeIngredients->count() >= 6) {
            $sebepler[] = 'malzeme listesi zengin';
        }

        return $sebepler === [] ? 'sıradan' : implode(', ', $sebepler);
    }
}
