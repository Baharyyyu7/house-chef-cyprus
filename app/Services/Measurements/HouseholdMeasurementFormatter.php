<?php

declare(strict_types=1);

namespace App\Services\Measurements;

use App\Models\Unit;
use Illuminate\Support\Str;
use Illuminate\Translation\MessageSelector;

final class HouseholdMeasurementFormatter
{
    /** Metrik birimler kesirle değil sayıyla okunur. */
    private const METRIK_TAM = ['g', 'gram', 'ml', 'mililitre'];

    private const METRIK_ONDALIK = ['kg', 'kilogram', 'l', 'litre'];

    /**
     * Türkçe ad sözlüğü: kanonik kod tablodaki adla eşleşmezse buraya düşer.
     * "1 cay_kasigi", "2 dis", "3 item" ekranda ham kod olarak basılıyordu.
     *
     * @var array<string, string>
     */
    private const TURKCE_ADLAR = [
        'g' => 'g', 'gram' => 'g', 'ml' => 'ml', 'mililitre' => 'ml', 'kg' => 'kg', 'kilogram' => 'kg', 'l' => 'l', 'litre' => 'l',
        'adet' => 'adet', 'item' => 'adet', 'cay_kasigi' => 'çay kaşığı', 'tatli_kasigi' => 'tatlı kaşığı', 'yemek_kasigi' => 'yemek kaşığı',
        'su_bardagi' => 'su bardağı', 'cay_bardagi' => 'çay bardağı', 'kahve_fincani' => 'kahve fincanı', 'dis' => 'diş', 'demet' => 'demet',
        'dal' => 'dal', 'tutam' => 'tutam', 'paket' => 'paket', 'dilim' => 'dilim', 'kutu' => 'kutu', 'avuc' => 'avuç', 'kase' => 'kase',
        'kepce' => 'kepçe', 'servis_kasigi' => 'servis kaşığı', 'yaprak' => 'yaprak', 'bag' => 'bağ', 'fiske' => 'fiske', 'konserve' => 'konserve',
    ];

    /**
     * Ev ölçüsü kesirle, metrik ölçü sayıyla basılır.
     *
     * Ölçeklenen tarif "4 yarım su bardağı" ve "1,08 paket Kuru Maya 10.833 g"
     * diyordu. Mutfakta kimse 1,08 paket ölçmez: ev ölçüsü en yakın ½ ⅓ ¼ ¾
     * kesrine yuvarlanır ("4½ su bardağı"), g ve ml tam sayıya.
     */
    public function quantity(string|int|float|null $quantity, ?Unit $unit = null): string
    {
        if ($quantity === null || bccomp((string) $quantity, '0', 4) === 0) {
            return '';
        }

        $sayi = (float) $quantity;

        if ($unit === null) {
            return $this->ondalik($sayi, 2);
        }

        $kod = (string) $unit->code;

        if (in_array($kod, self::METRIK_TAM, true) || in_array($kod, self::METRIK_ONDALIK, true)) {
            return $this->metrik($sayi, $kod);
        }

        return $this->kesir($sayi);
    }

    /** Kanonik satır ("≈ 360 g") için miktar: g/ml tam sayı. */
    public function kanonikMiktar(string|int|float|null $quantity, string $kod): string
    {
        if ($quantity === null || bccomp((string) $quantity, '0', 4) === 0) {
            return '';
        }

        return $this->metrik((float) $quantity, $kod);
    }

    /**
     * Sayılabilir malzemede İngilizce ad sayıya uyar: "1 egg yolk",
     * "3 onions". Birimi olan satırda ad yalın kalır ("2 tbsp flour").
     */
    public function adSayiyla(string $ad, string|int|float|null $miktar, string $birimEtiketi): string
    {
        if (app()->getLocale() !== 'en' || $birimEtiketi !== '' || $miktar === null || $ad === '') {
            return $ad;
        }

        return (float) $miktar > 1 ? Str::plural($ad) : Str::singular($ad);
    }

    /**
     * Kanonik satır ana satırdan farklı bir şey söylüyor mu? "adet" hiçbir
     * zaman söylemez (sayı ana satırda); "gram" ile "g" aynı birimdir.
     */
    public function ikinciSatirGerekli(?string $kanonik, ?string $gorunen): bool
    {
        $esle = ['item' => 'adet', 'gram' => 'g', 'mililitre' => 'ml', 'kilogram' => 'kg', 'litre' => 'l'];
        $kanonik = $esle[(string) $kanonik] ?? (string) $kanonik;
        $gorunen = $esle[(string) $gorunen] ?? (string) $gorunen;

        return $kanonik !== '' && $kanonik !== 'adet' && $kanonik !== $gorunen;
    }

    private function metrik(float $sayi, string $kod): string
    {
        if (in_array($kod, self::METRIK_ONDALIK, true)) {
            return $this->ondalik($sayi, 2);
        }

        // 10 g'ın altında ondalık bilgi taşır (0,5 g safran); üstünde gürültü.
        return $sayi < 10 ? $this->ondalik($sayi, 1) : (string) (int) round($sayi);
    }

    private function ondalik(float $sayi, int $basamak): string
    {
        $deger = rtrim(rtrim(number_format($sayi, $basamak, '.', ''), '0'), '.');

        return app()->getLocale() === 'en' ? $deger : str_replace('.', ',', $deger);
    }

    private function kesir(float $sayi): string
    {
        $tam = (int) floor($sayi);
        $artan = $sayi - $tam;
        $adaylar = ['' => 0.0, '¼' => 0.25, '⅓' => 1 / 3, '½' => 0.5, '⅔' => 2 / 3, '¾' => 0.75, '+1' => 1.0];
        if ($tam === 0) {
            $adaylar = ['⅛' => 0.125] + $adaylar;
            unset($adaylar['']);
        }
        $secilen = '';
        $fark = PHP_FLOAT_MAX;
        foreach ($adaylar as $isaret => $deger) {
            if (abs($artan - $deger) < $fark) {
                $fark = abs($artan - $deger);
                $secilen = (string) $isaret;
            }
        }
        if ($secilen === '+1') {
            return (string) ($tam + 1);
        }

        return ($tam === 0 ? '' : (string) $tam).$secilen;
    }

    public function measureLabel(Unit|string|null $unit, string|int|float|null $quantity = null): string
    {
        if (is_string($unit)) {
            return $unit;
        }
        if ($unit === null) {
            return '';
        }
        $isPlural = $quantity !== null && bccomp((string) $quantity, '1', 4) !== 0;
        $yabanci = $this->turkce() ? null : ($this->sozluk('birimler')[$unit->code] ?? null);

        if ($yabanci !== null) {
            return $yabanci === '' ? '' : (new MessageSelector)->choose($yabanci, $this->cogulSayisi($quantity), app()->getLocale());
        }

        return $isPlural ? ($unit->plural_name ?: $unit->name) : ($unit->singular_name ?: $unit->name);
    }

    /**
     * Kanonik birim kodunun ("g", "ml", "adet") o dildeki etiketi.
     * Türkçede insan adı döner ("çay kaşığı"); yabancı dilde karşılığı boşsa ("adet")
     * boş döner ve satır basılmaz, sayı zaten ana satırda yazıyor.
     */
    public function birimKodu(string $kod, string|int|float|null $miktar = null): string
    {
        // "item" içe aktarmadan gelen "adet"in İngilizcesi; ayrı bir birim değil.
        $kod = $kod === 'item' ? 'adet' : $kod;

        if ($this->turkce()) {
            return $this->turkceBirimAdi($kod);
        }

        $satir = $this->sozluk('birimler')[$kod] ?? null;

        return $satir === null ? $kod : ($satir === '' ? '' : (new MessageSelector)->choose($satir, $this->cogulSayisi($miktar), app()->getLocale()));
    }

    /**
     * Hazırlık notunun o dildeki karşılığı; sözlükte yoksa null.
     *
     * Not ("rendelenmiş", "halka doğranmış") tarif satırında Türkçe
     * duruyordu ve yabancı sayfada "4 adet rendelenmiş tomatoes" diye
     * basılıyordu. Karşılığı olmayan not yabancı dilde hiç basılmaz.
     */
    public function hazirlik(?string $not): ?string
    {
        $not = trim((string) $not);

        if ($not === '' || $this->turkce()) {
            return $not === '' ? null : $not;
        }

        return $this->sozluk('hazirlik')[mb_strtolower($not)] ?? null;
    }

    /**
     * Malzeme grubu başlığının o dildeki karşılığı; sözlükte yoksa null.
     *
     * 11 Ekim 2026'da canlıda: birden fazla gruplu 6 tarifin 30 açık yabancı
     * sayfası "Ana malzemeler", "Şerbet" başlığını Türkçe basıyordu.
     */
    public function grup(string $ad): ?string
    {
        return $this->turkce() ? $ad : ($this->sozluk('malzeme_gruplari')[mb_strtolower(trim($ad))] ?? null);
    }

    private function turkceBirimAdi(string $kod): string
    {
        /** @var array<string, string>|null $tablo */
        static $tablo = null;
        if (! isset(self::TURKCE_ADLAR[$kod])) {
            $tablo ??= Unit::query()->pluck('name', 'code')->map(fn ($ad): string => (string) $ad)->all();
        }

        return self::TURKCE_ADLAR[$kod] ?? ($tablo[$kod] ?? str_replace('_', ' ', $kod));
    }

    private function turkce(): bool
    {
        return app()->getLocale() === (string) config('locales.default', 'tr');
    }

    /** @return array<string, string> */
    private function sozluk(string $grup): array
    {
        $satirlar = trans($grup);

        return is_array($satirlar) ? $satirlar : [];
    }

    /**
     * Kesirli miktar Lehçe ve Rusçada ikinci biçimi alır ("1 ½ łyżki",
     * "½ стакана"); İngilizce, Almanca ve Portekizcede birden küçükse
     * tekil, büyükse çoğul.
     */
    private function cogulSayisi(string|int|float|null $miktar): int
    {
        $sayi = $miktar === null ? 1.0 : (float) $miktar;

        if (floor($sayi) === $sayi) {
            return (int) $sayi;
        }

        return $sayi < 1 && ! in_array(app()->getLocale(), ['pl', 'ru'], true) ? 1 : 2;
    }
}
