<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Models\Recipe;
use App\Models\SlugRedirect;
use Illuminate\Console\Command;

/**
 * Teslim edilen içerik çevirilerini içeri alır.
 *
 * Yalnız JSON okur. Bu dizine ajanlar yazıyor ve oradan PHP çalıştırmak
 * doğrudan uzaktan kod çalıştırma olurdu; JSON çalıştırılamaz.
 *
 * Kısmi çeviri kabul edilmez: bir kaydın çevrilebilir alanlarının hepsi
 * dolu değilse dosya reddedilir. Başlığı İngilizce, açıklaması Türkçe bir
 * sayfa, çevrilmemiş sayfanın kendisidir ve arama motoru tarafında aynı
 * içeriğin ikinci kopyası olarak görünür.
 *
 * Komut hiçbir koşulda dil etkinleştirmez; o karar insana ait.
 */
final class ImportContentTranslations extends Command
{
    protected $signature = 'content:import-translations
        {--apply : Değişiklikleri yaz. Varsayılan yalnız rapordur.}
        {--locale= : Yalnız tek bir dili işle}';

    protected $description = 'Teslim edilen içerik çevirilerini denetler ve içeri alır.';

    public function handle(): int
    {
        $yaz = (bool) $this->option('apply');
        $yalnizDil = (string) ($this->option('locale') ?? '');
        $kok = storage_path('app/content-import/translations');

        if (! is_dir($kok)) {
            $this->components->info('Teslim klasörü yok: '.$kok);

            return self::SUCCESS;
        }

        $desteklenen = (array) config('locales.supported', []);
        $varsayilan = (string) config('locales.default', 'tr');

        $alinan = 0;
        $atlanan = [];

        foreach (glob($kok.'/*', GLOB_ONLYDIR) ?: [] as $dilDizini) {
            $locale = basename($dilDizini);

            if ($yalnizDil !== '' && $locale !== $yalnizDil) {
                continue;
            }

            if (! array_key_exists($locale, $desteklenen)) {
                $atlanan['desteklenmeyen dil: '.$locale][] = $locale;

                continue;
            }

            if ($locale === $varsayilan) {
                $atlanan['kaynak dil çevrilmez: '.$locale][] = $locale;

                continue;
            }

            // Yalnız .json. Buraya bırakılan çalıştırılabilir dosya okunmaz.
            foreach (glob($dilDizini.'/*.php') ?: [] as $reddedilen) {
                $this->warn('Yok sayıldı (bu klasörde PHP çalıştırılmaz): '.basename($reddedilen));
            }

            foreach (glob($dilDizini.'/*.json') ?: [] as $dosya) {
                $ad = basename($dosya);
                $veri = json_decode((string) file_get_contents($dosya), true);

                if (! is_array($veri)) {
                    $atlanan['okunamayan JSON'][] = $ad;

                    continue;
                }

                if (! is_string($veri['locale'] ?? null) || trim($veri['locale']) === '') {
                    $atlanan['dil bilgisi eksik veya geçersiz'][] = $ad;

                    continue;
                }

                if ($veri['locale'] !== $locale) {
                    $atlanan['dosyanın dili klasörle uyuşmuyor'][] = $ad;

                    continue;
                }

                $tur = (string) ($veri['type'] ?? '');
                $slug = (string) ($veri['slug'] ?? '');
                $alanlar = is_array($veri['fields'] ?? null) ? $veri['fields'] : [];

                $kayit = $this->kayitBul($tur, $slug);

                if ($kayit === null) {
                    $atlanan[$tur === '' ? 'tür belirtilmemiş' : 'kayıt bulunamadı ('.$tur.')'][] = $ad;

                    continue;
                }

                $gerekli = $kayit->translatableFields();
                $eksik = array_values(array_filter(
                    $gerekli,
                    static fn (string $alan): bool => ! is_string($alanlar[$alan] ?? null) || trim((string) $alanlar[$alan]) === ''
                ));

                if ($eksik !== []) {
                    $atlanan['eksik alan: '.implode(', ', $eksik)][] = $ad;

                    continue;
                }

                // Türkçe metnin olduğu gibi kopyalanması alanı doluymuş gibi
                // gösterir ama sayfa Türkçe kalır; engellemeye çalıştığımız
                // durumun ta kendisi.
                $aynisi = array_values(array_filter(
                    $gerekli,
                    static fn (string $alan): bool => trim((string) $alanlar[$alan]) === trim((string) $kayit->getAttribute($alan))
                ));

                if ($aynisi !== []) {
                    $atlanan['kaynak metnin kopyası: '.implode(', ', $aynisi)][] = $ad;

                    continue;
                }

                // Birebir kopya yakalanıyordu ama asıl kaçak bu değildi:
                // "Türkçe Başlık – Kompletny przewodnik" gibi, kaynağın
                // tamamını içinde taşıyıp sonuna çevrilmiş bir kuyruk ekleyen
                // başlıklar eşitlik testini geçiyordu. Ölçüldüğünde
                // indekslenebilir sayfaların 590 başlığı bu durumdaydı:
                // Rusça arayan kullanıcı Türkçe bir başlık görüyordu ve
                // Rusça 334 gösterimde %0,3 tıklanma alıyordu. Kuyruk
                // eklemek çeviri değildir; kaynağın kendisi başlığın içinde
                // duruyorsa alan çevrilmemiştir.
                $gomulu = array_values(array_filter(
                    $gerekli,
                    fn (string $alan): bool => $this->kaynagiIcindeTasiyor(
                        (string) $alanlar[$alan],
                        (string) $kayit->getAttribute($alan)
                    )
                ));

                if ($gomulu !== []) {
                    $atlanan['kaynak metin çevirinin içinde: '.implode(', ', $gomulu)][] = $ad;

                    continue;
                }

                // Tarifin gövdesi kendi sütunlarında değil, adımlarında ve
                // malzemelerinde durur. Başlığı İngilizce, yönergesi Türkçe
                // bir sayfa çevrilmemiş sayfanın kendisidir; dosya da öyle.
                $cocukIsleri = [];

                if ($tur === 'recipe') {
                    $cocukSorun = $this->tarifGovdesi($kayit, $veri, $cocukIsleri);

                    if ($cocukSorun !== null) {
                        $atlanan[$cocukSorun][] = $ad;

                        continue;
                    }
                }

                if (! $yaz) {
                    $alinan++;

                    continue;
                }

                foreach ($gerekli as $alan) {
                    $kayit->translations()->updateOrCreate(
                        ['locale' => $locale, 'field' => $alan],
                        ['value' => trim((string) $alanlar[$alan])]
                    );
                }

                foreach ($cocukIsleri as [$cocuk, $cocukAlan, $cocukDeger]) {
                    $cocuk->translations()->updateOrCreate(
                        ['locale' => $locale, 'field' => $cocukAlan],
                        ['value' => $cocukDeger]
                    );
                }

                $alinan++;
            }
        }

        $this->newLine();
        $this->components->info($alinan.($yaz ? ' kayıt çevirisi içeri alındı.' : ' kayıt içeri alınmaya hazır.'));

        foreach ($atlanan as $sebep => $liste) {
            $this->components->warn(count($liste).' atlandı — '.$sebep);
            foreach (array_slice($liste, 0, 5) as $ad) {
                $this->line('    · '.$ad);
            }
        }

        if (! $yaz) {
            $this->newLine();
            $this->line('Yazmak için: <options=bold>php artisan content:import-translations --apply</>');
        }

        $this->newLine();
        $this->line('Çeviri içeri girmek dili açmaz; dil etkinleştirmesi ayrı ve insana ait bir karardır.');

        return self::SUCCESS;
    }

    /**
     * Tarifin adım ve malzeme çevirilerini denetler.
     *
     * Yazmaz; yalnız yapılacak işleri toplar. Böylece bir eksik bulunduğunda
     * dosyanın yarısı yazılmış olmaz - yarım yazılmış tarif, kapıdan geçen
     * ama gövdesi Türkçe kalan sayfa demektir.
     *
     * @param  array<string, mixed>  $veri
     * @param  list<array{0: object, 1: string, 2: string}>  $isler
     * @return string|null Sorun varsa atlanma sebebi, yoksa null
     */
    private function tarifGovdesi(Recipe $tarif, array $veri, array &$isler): ?string
    {
        $adimCevirileri = is_array($veri['steps'] ?? null) ? $veri['steps'] : [];
        $malzemeCevirileri = is_array($veri['ingredients'] ?? null) ? $veri['ingredients'] : [];

        foreach ($tarif->steps as $adim) {
            $gelen = null;
            foreach ($adimCevirileri as $aday) {
                if (is_array($aday) && (int) ($aday['position'] ?? 0) === (int) $adim->position) {
                    $gelen = $aday;
                    break;
                }
            }

            $sorun = $this->cocukAlanlari($adim, $gelen, 'adım '.$adim->position, $isler);

            if ($sorun !== null) {
                return $sorun;
            }
        }

        foreach ($tarif->ingredients as $malzeme) {
            $gelen = null;
            foreach ($malzemeCevirileri as $aday) {
                if (is_array($aday) && (string) ($aday['slug'] ?? '') === (string) $malzeme->slug) {
                    $gelen = $aday;
                    break;
                }
            }

            $sorun = $this->cocukAlanlari($malzeme, $gelen, 'malzeme '.$malzeme->slug, $isler);

            if ($sorun !== null) {
                return $sorun;
            }
        }

        return null;
    }

    /**
     * Bir çocuk kaydın çevrilebilir alanlarını denetler.
     *
     * Kaynakta boş olan alan istenmez: Türkçesi boş bir ipucunun İngilizcesini
     * şart koşmak, çevirisi tamam olan tarifi asla açılmayacak bir eksikle
     * kilitlerdi.
     *
     * @param  array<string, mixed>|null  $gelen
     * @param  list<array{0: object, 1: string, 2: string}>  $isler
     */
    private function cocukAlanlari(object $kayit, ?array $gelen, string $etiket, array &$isler): ?string
    {
        foreach ($kayit->translatableFields() as $alan) {
            $kaynak = trim((string) $kayit->getRawOriginal($alan));

            if ($kaynak === '') {
                continue;
            }

            $deger = is_string($gelen[$alan] ?? null) ? trim((string) $gelen[$alan]) : '';

            if ($deger === '') {
                return 'gövde çevrilmemiş ('.$etiket.': '.$alan.')';
            }

            if ($deger === $kaynak) {
                return 'gövdede kaynak metnin kopyası ('.$etiket.': '.$alan.')';
            }

            $isler[] = [$kayit, $alan, $deger];
        }

        return null;
    }

    /**
     * Verilen tür ve slug için veritabanındaki asıl kaydı bulur.
     * Birebir eşleşme, eski yönlendirme (SlugRedirect), public ID soneki veya önek temizleme dener.
     */
    /**
     * Çeviri, kaynak metni olduğu gibi içinde taşıyor mu?
     *
     * Yalnız başlık ve özet gibi kısa alanlar için anlamlı. Gövdede bir
     * özel adın ya da alıntının aynen geçmesi olağandır; başlıkta kaynağın
     * tamamının geçmesi ise çeviri yapılmadığının kanıtıdır.
     */
    private function kaynagiIcindeTasiyor(string $ceviri, string $kaynak): bool
    {
        $kaynak = trim($kaynak);
        $ceviri = trim($ceviri);

        if ($kaynak === '' || $ceviri === '' || $ceviri === $kaynak) {
            return false;
        }

        // Çok kısa kaynaklar tesadüfen eşleşebilir; "Hellim" başlıklı bir
        // tarifin adı çeviride de geçebilir ve bu doğrudur.
        if (mb_strlen($kaynak) < 20) {
            return false;
        }

        return mb_strpos($ceviri, $kaynak) !== false;
    }

    private function kayitBul(string $tur, string $slug): Recipe|BlogPost|null
    {
        if ($tur === 'recipe') {
            $recipe = Recipe::query()->where('slug', $slug)->first();
            if ($recipe !== null) {
                return $recipe;
            }

            // Public ID soneki (-XXXXX) denemesi
            $recipe = Recipe::query()->where('slug', 'like', $slug.'-%')->first();
            if ($recipe !== null) {
                return $recipe;
            }

            // SlugRedirect denemesi
            $redirect = SlugRedirect::query()
                ->where('old_slug', $slug)
                ->where('model_type', Recipe::class)
                ->first();
            if ($redirect !== null) {
                return Recipe::query()->find($redirect->model_id);
            }

            return null;
        }

        if ($tur === 'blog') {
            $post = BlogPost::query()->where('slug', $slug)->first();
            if ($post !== null) {
                return $post;
            }

            // Public ID soneki (-XXXXX) denemesi
            $post = BlogPost::query()->where('slug', 'like', $slug.'-%')->first();
            if ($post !== null) {
                return $post;
            }

            // SlugRedirect denemesi
            $redirect = SlugRedirect::query()
                ->where('old_slug', $slug)
                ->where('model_type', BlogPost::class)
                ->first();
            if ($redirect !== null) {
                return BlogPost::query()->find($redirect->model_id);
            }

            // "geo-2026-" öneki temizleme
            if (str_starts_with($slug, 'geo-2026-')) {
                $clean = substr($slug, 9);
                $post = BlogPost::query()->where('slug', $clean)->first()
                    ?? BlogPost::query()->where('slug', 'like', $clean.'-%')->first()
                    ?? BlogPost::query()->where('slug', 'like', '%'.$clean.'%')->first();
                if ($post !== null) {
                    return $post;
                }
            }

            return null;
        }

        return null;
    }
}
