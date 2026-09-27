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
        $this->assertSame('Yarım', $bicim->quantity('0.5', $this->birim('su_bardagi')));
        $this->assertSame('rendelenmiş', $bicim->hazirlik('rendelenmiş'));
    }

    public function test_units_fractions_and_notes_are_translated(): void
    {
        $bicim = app(HouseholdMeasurementFormatter::class);

        app()->setLocale('en');
        $this->assertSame('tbsp', $bicim->measureLabel($this->birim('yemek_kasigi'), '3'));
        $this->assertSame('', $bicim->measureLabel($this->birim('adet'), '4'));
        $this->assertSame('glass', $bicim->measureLabel($this->birim('su_bardagi'), '0.5'));
        $this->assertSame('glasses', $bicim->measureLabel($this->birim('su_bardagi'), '1.5'));
        $this->assertSame('1 ½', $bicim->quantity('1.5', $this->birim('su_bardagi')));
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

    public function test_a_note_without_a_translation_is_dropped_not_printed_in_turkish(): void
    {
        app()->setLocale('de');

        $this->assertNull(app(HouseholdMeasurementFormatter::class)->hazirlik('annemin usulü doğranmış'));
    }

    public function test_every_locale_has_the_same_units_and_notes(): void
    {
        $birimler = array_keys(require lang_path('en/birimler.php'));
        $notlar = array_keys(require lang_path('en/hazirlik.php'));

        foreach (['de', 'fa', 'pl', 'pt', 'ru'] as $dil) {
            $this->assertSame($birimler, array_keys(require lang_path($dil.'/birimler.php')), $dil);
            $this->assertSame($notlar, array_keys(require lang_path($dil.'/hazirlik.php')), $dil);
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
