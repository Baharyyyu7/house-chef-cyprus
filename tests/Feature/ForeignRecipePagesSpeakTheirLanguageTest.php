<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Unit;
use App\Services\Measurements\HouseholdMeasurementFormatter;
use Tests\TestCase;

/**
 * Yabancı tarif sayfasının malzeme satırı o dilde okunur.
 *
 * 27 Eylül 2026'da canlıda ölçüldü: İngilizce Menemen sayfası "4 adet
 * rendelenmiş tomatoes", "3 yemek kaşığı olive oil" diyordu; birim adları
 * ve hazırlık notu hiçbir dilde çevrilmiyordu. Almanca ve Farsça sayfanın
 * başlığı "Ingredients", rozeti ham ":count Zutaten" idi.
 */
final class ForeignRecipePagesSpeakTheirLanguageTest extends TestCase
{
    private function birim(string $kod): Unit
    {
        return new Unit(['code' => $kod, 'name' => 'yemek kaşığı', 'singular_name' => 'yemek kaşığı', 'plural_name' => 'yemek kaşığı', 'supports_fractions' => true, 'precision' => 2]);
    }

    public function test_turkish_keeps_its_own_words(): void
    {
        app()->setLocale('tr');
        $bicim = app(HouseholdMeasurementFormatter::class);

        $this->assertSame('yemek kaşığı', $bicim->measureLabel($this->birim('yemek_kasigi'), '3'));
        $this->assertSame('½', $bicim->quantity('0.5', $this->birim('su_bardagi')));
        $this->assertSame('rendelenmiş', $bicim->hazirlik('rendelenmiş'));
    }

    public function test_units_fractions_and_notes_are_translated(): void
    {
        $bicim = app(HouseholdMeasurementFormatter::class);

        app()->setLocale('en');
        $this->assertSame('tbsp', $bicim->measureLabel($this->birim('yemek_kasigi'), '3'));
        $this->assertSame('', $bicim->measureLabel($this->birim('adet'), '4'));
        // "glass" İngilizce mutfakta ölçü değil; su bardağı 200 ml'lik cup.
        $this->assertSame('cup (200 ml)', $bicim->measureLabel($this->birim('su_bardagi'), '0.5'));
        $this->assertSame('cups (200 ml)', $bicim->measureLabel($this->birim('su_bardagi'), '1.5'));
        $this->assertSame('1½', $bicim->quantity('1.5', $this->birim('su_bardagi')));
        $this->assertSame('1.25', $bicim->quantity('1.25'));
        $this->assertSame('grated', $bicim->hazirlik('Rendelenmiş'));

        app()->setLocale('pl');
        $this->assertSame('łyżki', $bicim->measureLabel($this->birim('yemek_kasigi'), '3'));
        $this->assertSame('łyżek', $bicim->measureLabel($this->birim('yemek_kasigi'), '5'));
        $this->assertSame('szklanki', $bicim->measureLabel($this->birim('su_bardagi'), '1.5'));

        app()->setLocale('ru');
        $this->assertSame('шт.', $bicim->measureLabel($this->birim('adet'), '4'));
        $this->assertSame('стакана', $bicim->measureLabel($this->birim('su_bardagi'), '0.5'));
    }

    public function test_canonical_unit_code_is_localized_or_dropped(): void
    {
        $bicim = app(HouseholdMeasurementFormatter::class);

        app()->setLocale('tr');
        $this->assertSame('adet', $bicim->birimKodu('adet', '4'));

        app()->setLocale('en');
        $this->assertSame('', $bicim->birimKodu('adet', '4'));
        $this->assertSame('ml', $bicim->birimKodu('ml', '45'));

        app()->setLocale('ru');
        $this->assertSame('г', $bicim->birimKodu('g', '100'));
    }

    public function test_a_note_without_a_translation_is_dropped_not_printed_in_turkish(): void
    {
        app()->setLocale('de');

        $this->assertNull(app(HouseholdMeasurementFormatter::class)->hazirlik('annemin usulü doğranmış'));
    }

    public function test_ingredient_group_heading_is_translated_or_dropped(): void
    {
        $bicim = app(HouseholdMeasurementFormatter::class);

        app()->setLocale('tr');
        $this->assertSame('Şerbet', $bicim->grup('Şerbet'));

        app()->setLocale('en');
        $this->assertSame('Main ingredients', $bicim->grup('Ana malzemeler'));
        $this->assertSame('Syrup', $bicim->grup('Şerbet'));

        app()->setLocale('de');
        $this->assertNull($bicim->grup('Hamur için'));
    }

    public function test_every_locale_has_the_same_units_and_notes(): void
    {
        $birimler = array_keys(require lang_path('en/birimler.php'));
        $notlar = array_keys(require lang_path('en/hazirlik.php'));
        $gruplar = array_keys(require lang_path('en/malzeme_gruplari.php'));

        foreach (['de', 'fa', 'pl', 'pt', 'ru'] as $dil) {
            $this->assertSame($birimler, array_keys(require lang_path($dil.'/birimler.php')), $dil);
            $this->assertSame($notlar, array_keys(require lang_path($dil.'/hazirlik.php')), $dil);
            $this->assertSame($gruplar, array_keys(require lang_path($dil.'/malzeme_gruplari.php')), $dil);
        }
    }

    public function test_recipe_ui_strings_are_not_english_copies(): void
    {
        $en = json_decode((string) file_get_contents(lang_path('en.json')), true);

        foreach (['de', 'fa', 'pl', 'pt', 'ru'] as $dil) {
            $satirlar = json_decode((string) file_get_contents(lang_path($dil.'.json')), true);

            foreach (['recipe.ingredients', 'recipe.servings', 'recipe.instructions', 'recipe.prep', 'recipe.difficulty'] as $anahtar) {
                $this->assertNotSame($en[$anahtar], $satirlar[$anahtar], $dil.' '.$anahtar);
            }

            $this->assertStringContainsString(':n', $satirlar['recipe.show.ingredients_count'], $dil);
        }
    }
}
