<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\BlogPost;
use App\Models\Recipe;
use App\Models\SlugRedirect;

/**
 * Yazı gövdesindeki tarif ve yazı bağlantılarının o dildeki canlı adresi.
 *
 * Gövde bağlantısı yazıldığı gün doğruydu; hedef sonradan taslağa alınınca
 * ya da slug'ı değişince sessizce bozuldu. 13 Ekim 2026'da yayındaki 100
 * yazının gövdelerindeki 157 adresten 50'si 404, 38'i yönlendirmeyle
 * açılıyordu; çeviri gövdeleri Türkçe sayfaya gidiyordu (Almanca yazıdan
 * /tarifler/sutlac, oysa /de/rezepte/sutlac var). content:link-integrity
 * yalnız hub sayfalarını tarıyor, gövdeye bakmıyordu.
 */
final class YaziIcBaglantilari
{
    /** Markdown bağlantısı; görsel (![..]) hariç. 1: çapa, 2: adres, 3: başlık. */
    public const DESEN = '#(?<!!)\[([^\]]*)\]\(((?:https?://(?:www\.)?housechefcyprus\.com)?/[^)\s]*)(\s+"[^"]*")?\)#u';

    /** @var array<string, Recipe|BlogPost|null> */
    private array $kayitlar = [];

    public function __construct(private readonly LocaleUrlService $diller) {}

    /**
     * Bağlantının gövde dilindeki, yönlendirmesiz canlı adresi.
     *
     * Tarif ve yazı dışındaki yollar denetlenmez, olduğu gibi döner.
     * null: bu dilde açık bir sayfa yok.
     */
    public function hedef(string $adres, string $dil): ?string
    {
        $yol = (string) preg_replace('#^https?://(?:www\.)?housechefcyprus\.com#', '', $adres);
        $icYol = $yol;

        foreach (array_keys($this->diller->enabledLocales()) as $onek) {
            if (str_starts_with($yol, '/'.$onek.'/')) {
                $icYol = $this->diller->icYol($onek, substr($yol, strlen('/'.$onek)));
                break;
            }
        }

        if (! preg_match('#^/(tarifler|blog)/([^/?\#]+)/?$#u', $icYol, $m)) {
            return $adres;
        }

        $kayit = $this->kayit($m[1], rawurldecode($m[2]));

        if ($kayit === null || ($dil !== $this->diller->defaultLocale() && ! $kayit->hasTranslation($dil))) {
            return null;
        }

        $tur = $kayit instanceof Recipe ? 'tarifler' : 'blog';

        return (string) parse_url($this->diller->urlFor($dil, '/'.$tur.'/'.$kayit->getAttribute('slug')), PHP_URL_PATH);
    }

    /**
     * Gövde dilinde canlı sayfaya doğrudan gitmeyen bağlantılar.
     *
     * Yalnız yazının o dilde açık olduğu gövdeler okunur.
     *
     * @return list<array{dil: string, adres: string, hedef: string|null}>
     */
    public function kusurlar(BlogPost $yazi): array
    {
        $kusurlar = [];

        foreach ($this->govdeler($yazi) as $dil => $govde) {
            preg_match_all(self::DESEN, $govde, $eslesmeler, PREG_SET_ORDER);

            foreach ($eslesmeler as $e) {
                $adres = (string) preg_replace('#^https?://(?:www\.)?housechefcyprus\.com#', '', $e[2]);
                $hedef = $this->hedef($adres, $dil);

                if ($hedef !== $adres) {
                    $kusurlar[] = ['dil' => $dil, 'adres' => $adres, 'hedef' => $hedef];
                }
            }
        }

        return $kusurlar;
    }

    /** @return array<string, string> */
    private function govdeler(BlogPost $yazi): array
    {
        $govdeler = [$this->diller->defaultLocale() => (string) $yazi->getRawOriginal('body')];

        foreach ($yazi->translations()->where('field', 'body')->get() as $ceviri) {
            if (isset($this->diller->enabledLocales()[$ceviri->locale]) && $yazi->hasTranslation($ceviri->locale)) {
                $govdeler[$ceviri->locale] = (string) $ceviri->value;
            }
        }

        return $govdeler;
    }

    /**
     * Yayındaki kayıt; yoksa eski adresin yönlendirdiği ya da sitenin ikiz
     * diye açtığı kayıt. Slug harf duyarsız: MySQL öyle eşleşiyor ve eski
     * blog slug'larında büyük harfli ek var.
     */
    private function kayit(string $tur, string $slug): Recipe|BlogPost|null
    {
        $anahtar = $tur.'|'.mb_strtolower($slug);

        if (array_key_exists($anahtar, $this->kayitlar)) {
            return $this->kayitlar[$anahtar];
        }

        $acik = static fn (string $sinif) => $sinif === Recipe::class
            ? Recipe::query()->where('status', 'published')->where('verification_status', 'verified')
            : BlogPost::query()->publiclyVisible();
        $sinif = $tur === 'tarifler' ? Recipe::class : BlogPost::class;
        $ayni = static fn ($q, string $s) => $q->whereRaw('LOWER(slug) = ?', [mb_strtolower($s)]);

        $kayit = $ayni($acik($sinif), $slug)->first();

        if ($kayit === null) {
            $yonlendirme = SlugRedirect::query()->where('old_slug', $slug)->first();
            if ($yonlendirme !== null) {
                $kayit = $acik($yonlendirme->model_type === Recipe::class ? Recipe::class : BlogPost::class)->find($yonlendirme->model_id);
            }
        }

        // RecipeController ve BlogController yayında olmayan "-XXXXX" ekli
        // adresi kök adlı ikizine 301'liyor; bağlantı da oraya gitsin.
        $kok = (string) preg_replace('/-(?:taslak-inceleme-)?[A-Za-z0-9]{5}$/', '', $slug);
        if ($kayit === null && $kok !== $slug && $ayni($sinif::query(), $slug)->exists()) {
            $kayit = $acik($sinif)->where(fn ($q) => $q->where('slug', $kok)->orWhere('slug', 'like', $kok.'-%'))->first();
        }

        return $this->kayitlar[$anahtar] = $kayit;
    }
}
