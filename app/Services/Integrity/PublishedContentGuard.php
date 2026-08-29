<?php

declare(strict_types=1);

namespace App\Services\Integrity;

use App\Models\BlogPost;
use App\Models\Recipe;
use Illuminate\Support\Collection;

/**
 * Yayındaki içeriği bilinen bozulma desenlerine karşı tarar.
 *
 * Bu sitede arıza hep aynı biçimde geldi: bozuk içerik sessizce yayına
 * girdi ve haftalarca fark edilmedi. 3.649 makine üretimi sayfa, malzemesi
 * "500 g Ana Malzeme" olan 34 tarif, adımları "Gereken şekilde pişirin"
 * olan 38 tarif, mercimek çorbası diye kırmızı et ve balık isteyen 13
 * tarif, editoryal kapıdan geçmeden yayına girmiş 7 blog yazısı. Hepsi
 * "published" ve "verified" damgası taşıyordu.
 *
 * Bu yüzden nöbetçi içerik üretmiyor, yalnız bakıyor. Bulduklarını
 * raporluyor; istenirse açıkça bozuk olanları yayından alıyor. Silmiyor.
 */
final class PublishedContentGuard
{
    /** Malzeme listesinde asıl malzemenin yerine geçen yer tutucu. */
    private const YER_TUTUCU_MALZEME = 'Ana Malzeme';

    /** Yapılış adımı yerine geçen kalıplar. */
    private const YER_TUTUCU_ADIMLAR = ['Gereken şekilde pişirin', 'Uygun şekilde doğrayıp karıştırın'];

    /**
     * Başlığında geçtiğinde malzeme listesinde de beklenen sözcükler.
     *
     * Liste algoritmayla türetilemiyor: "kabak tatlısı" balkabağıyla
     * yapılır, "tavuklu kolokas" kuzuyla. Bu yüzden yalnız kökü kısa ve
     * ayırt edici olan, karşılığı doğrudan bir malzeme olan sözcükler var.
     */
    private const BASLIK_MALZEME = [
        'mercimek', 'nohut', 'patlıcan', 'tavuk', 'balık', 'ıspanak',
        'bulgur', 'patates', 'lahana', 'brokoli', 'karnabahar',
        'bakla', 'barbunya', 'bezelye', 'kereviz', 'pırasa', 'semizotu',
    ];

    /** @return array<string, mixed> */
    public function scan(bool $enforce = false): array
    {
        $bulgular = [
            'placeholder_ingredients' => $this->yerTutucuMalzemeli(),
            'placeholder_steps' => $this->yerTutucuAdimli(),
            'title_ingredient_mismatch' => $this->baslikMalzemeCeliskisi(),
            'blog_missing_editorial_gate' => $this->kapidanGecmeyenBlog(),
        ];

        $alinan = [];

        if ($enforce) {
            // Yalnız tarif tarafı otomatik alınıyor: bir tarifin malzemesi
            // yoksa ya da tarifle çelişiyorsa ziyaretçi onu pişiremez, bu
            // tartışmasız. Blog tarafı editoryal bir karar; raporlanıyor.
            foreach (['placeholder_ingredients', 'placeholder_steps', 'title_ingredient_mismatch'] as $tur) {
                foreach ($bulgular[$tur] as $slug) {
                    $tarif = Recipe::query()->where('slug', $slug)->first();

                    if ($tarif !== null && $tarif->status === 'published') {
                        $tarif->status = 'draft';
                        $tarif->save();
                        $alinan[] = $slug;
                    }
                }
            }
        }

        $toplam = array_sum(array_map('count', $bulgular));

        return [
            'status' => $toplam === 0 ? 'clean' : 'issues_found',
            'checked_at' => now()->toIso8601String(),
            'published_recipes' => $this->yayindakiTarifler()->count(),
            'findings' => $bulgular,
            'total' => $toplam,
            'enforced' => $enforce,
            'withdrawn' => array_values(array_unique($alinan)),
        ];
    }

    /** @return Collection<int, Recipe> */
    private function yayindakiTarifler(): Collection
    {
        return Recipe::query()
            ->where('status', 'published')
            ->with(['recipeIngredients.ingredient', 'steps'])
            ->get();
    }

    /** @return list<string> */
    private function yerTutucuMalzemeli(): array
    {
        return $this->yayindakiTarifler()
            ->filter(fn (Recipe $tarif): bool => $this->malzemeAdlari($tarif)->contains(self::YER_TUTUCU_MALZEME))
            ->pluck('slug')
            ->values()
            ->all();
    }

    /** @return list<string> */
    private function yerTutucuAdimli(): array
    {
        return $this->yayindakiTarifler()
            ->filter(function (Recipe $tarif): bool {
                $metin = $tarif->steps->pluck('instruction')->implode(' ');

                $eslesme = 0;
                foreach (self::YER_TUTUCU_ADIMLAR as $kalip) {
                    if (str_contains($metin, $kalip)) {
                        $eslesme++;
                    }
                }

                return $eslesme >= 2;
            })
            ->pluck('slug')
            ->values()
            ->all();
    }

    /** @return list<string> */
    private function baslikMalzemeCeliskisi(): array
    {
        return $this->yayindakiTarifler()
            ->reject(fn (Recipe $tarif): bool => $this->malzemeAdlari($tarif)->contains(self::YER_TUTUCU_MALZEME))
            ->filter(function (Recipe $tarif): bool {
                $baslik = mb_strtolower($tarif->title, 'UTF-8');
                $malzemeler = $this->malzemeAdlari($tarif)->map(fn (string $ad): string => mb_strtolower($ad, 'UTF-8'));

                foreach (self::BASLIK_MALZEME as $sozcuk) {
                    $kok = mb_substr($sozcuk, 0, 5, 'UTF-8');

                    if (! str_contains($baslik, $kok)) {
                        continue;
                    }

                    if (! $malzemeler->contains(fn (string $ad): bool => str_contains($ad, $kok))) {
                        return true;
                    }
                }

                return false;
            })
            ->pluck('slug')
            ->values()
            ->all();
    }

    /** @return list<string> */
    private function kapidanGecmeyenBlog(): array
    {
        return BlogPost::query()
            ->where('status', 'published')
            ->get()
            ->filter(fn (BlogPost $yazi): bool => $yazi->editorial_status !== 'approved'
                || $yazi->fact_check_status !== 'passed'
                || $yazi->originality_status !== 'passed')
            ->pluck('slug')
            ->values()
            ->all();
    }

    /** @return Collection<int, string> */
    private function malzemeAdlari(Recipe $tarif): Collection
    {
        return $tarif->recipeIngredients->map(fn ($satir): string => (string) ($satir->ingredient->name ?? ''));
    }
}
