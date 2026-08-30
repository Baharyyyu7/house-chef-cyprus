<?php

declare(strict_types=1);

namespace App\Services\Integrity;

use App\Contracts\Search\RecipeSearchInterface;
use Illuminate\Support\Facades\Http;

/**
 * Sitenin kendi iç bağlantıları gerçekten bir yere götürüyor mu?
 *
 * İki ayrı kusur var ve ikisi de hiçbir denetime takılmıyordu:
 *
 * 1. Kırık bağlantı - 404 dönen adres. Sayfa açılıyor, bağlantı duruyor,
 *    tıklayan hiçbir yere varmıyor.
 * 2. Boş filtre - 200 dönen ama sıfır tarif listeleyen katalog adresi. Bu
 *    daha sinsi: HTTP sağlıklı, sayfa açılıyor, içerik yok.
 *
 * İkincisi üretimde vardı: anasayfadaki "Kıbrıs mutfağı" çipi ve "Kıbrıs
 * mutfağını keşfet" bağlantısı `?cuisine=kibris` kullanıyordu. O slug hiçbir
 * mutfağa karşılık gelmiyor ve sıfır sonuç dönüyordu - sitenin amiral
 * konusundaki iki öne çıkan bağlantı boş sayfaya götürüyordu. Aylarca öyle
 * kaldı çünkü hiçbir şey bakmıyordu.
 *
 * Boş filtre kontrolü HTTP üzerinden değil arama servisinin kendisiyle
 * yapılıyor: sayfanın işaretlemesine bakmak, bir CSS sınıfı değişince sessizce
 * bozulan bir denetim demek olurdu.
 */
final class LinkIntegrityProbe
{
    /** Katalogda anlamlı olan filtre anahtarları; kontrol bunlara bakar. */
    private const FILTRE_ANAHTARLARI = [
        'q', 'cuisine', 'category', 'difficulty', 'dietary_tag', 'exclude_allergens',
        'max_minutes', 'min_protein', 'max_calories',
    ];

    public function __construct(private readonly RecipeSearchInterface $search) {}

    /**
     * @param list<string> $sayfalar taranacak hub adresleri
     * @return array<string, mixed>
     */
    public function probe(array $sayfalar, int $sinir = 60): array
    {
        $adresler = [];

        foreach ($sayfalar as $sayfa) {
            foreach ($this->baglantilar($sayfa) as $adres) {
                $adresler[$adres] = true;
            }
        }

        $adresler = array_slice(array_keys($adresler), 0, max(1, $sinir));
        $bulgular = [];

        foreach ($adresler as $adres) {
            $bulgu = $this->tekBaglanti($adres);

            if ($bulgu !== null) {
                $bulgular[] = $bulgu;
            }
        }

        return [
            'status' => $bulgular === [] ? 'ok' : 'issues_found',
            'checked_at' => now()->toIso8601String(),
            'checked' => count($adresler),
            // Manşet sayı: azalması iyileşmedir.
            'broken' => count($bulgular),
            'findings' => $bulgular,
        ];
    }

    /**
     * Bir sayfadaki kendi alan adımıza ait bağlantılar.
     *
     * @return list<string>
     */
    public function baglantilar(string $sayfa): array
    {
        try {
            $cevap = Http::timeout(15)->get($sayfa);
        } catch (\Throwable) {
            return [];
        }

        if (! $cevap->successful()) {
            return [];
        }

        $taban = rtrim((string) config('app.url'), '/');
        $bulunan = [];

        preg_match_all('#href="([^"]+)"#i', $cevap->body(), $eslesme);

        foreach ($eslesme[1] as $adres) {
            $adres = trim(html_entity_decode($adres, ENT_QUOTES | ENT_HTML5));

            // Çapa, e-posta, telefon ve dış bağlantılar bizim sorumluluğumuzda
            // değil.
            if ($adres === '' || ! str_starts_with($adres, $taban)) {
                continue;
            }

            $bulunan[strtok($adres, '#')] = true;
        }

        return array_keys($bulunan);
    }

    /** @return array<string, mixed>|null kusur yoksa null */
    private function tekBaglanti(string $adres): ?array
    {
        $parcalar = parse_url($adres);
        $yol = $parcalar['path'] ?? '/';

        // Katalog bağlantısı filtreliyse sıfır sonuç dönüp dönmediğine
        // bakılıyor: 200 dönen boş sayfa, kırık bağlantı kadar zararlı.
        if ($yol === '/tarifler' && isset($parcalar['query'])) {
            parse_str($parcalar['query'], $sorgu);
            $filtreler = array_intersect_key($sorgu, array_flip(self::FILTRE_ANAHTARLARI));

            if ($filtreler !== [] && $this->search->search($filtreler)->total() === 0) {
                return ['url' => $adres, 'reason' => 'EMPTY_FILTER', 'detail' => 'Bağlantı hiçbir tarif listelemiyor.'];
            }
        }

        try {
            $cevap = Http::timeout(15)->get($adres);
        } catch (\Throwable $hata) {
            return ['url' => $adres, 'reason' => 'UNREACHABLE', 'detail' => $hata->getMessage()];
        }

        if (! $cevap->successful()) {
            return ['url' => $adres, 'reason' => 'HTTP_'.$cevap->status()];
        }

        return null;
    }
}
