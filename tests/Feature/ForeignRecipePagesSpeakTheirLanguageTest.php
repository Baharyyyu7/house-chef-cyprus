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
        // "glass" İngilizce mutfakta ölçü değil; su bardağı 200 ml'lik cup ve
        // parantez toplamı söyler: "½ cup (200 ml)" toplam gibi okunuyordu.
        $this->assertSame('cup (100 ml)', $bicim->measureLabel($this->birim('su_bardagi'), '0.5'));
        $this->assertSame('cups (300 ml)', $bicim->measureLabel($this->birim('su_bardagi'), '1.5'));
        $this->assertSame('cup', $bicim->measureLabel($this->birim('su_bardagi')));
        // 1,125 bardak "1" basılır; etiket de tekil kalır.
        $this->assertSame('1', $bicim->quantity('1.125', $this->birim('su_bardagi')));
        $this->assertSame('cup (225 ml)', $bicim->measureLabel($this->birim('su_bardagi'), '1.125'));
        $this->assertSame('onion', $bicim->adSayiyla('onion', '1.125', ''));
        $this->assertSame('onions', $bicim->adSayiyla('onion', '2.25', ''));
        // Hacmi söyleyen etiketin altına "100 ml" bir daha basılmaz; gramda basılır.
        $this->assertFalse($bicim->ikinciSatirGerekli('ml', 'su_bardagi'));
        $this->assertTrue($bicim->ikinciSatirGerekli('g', 'su_bardagi'));
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
        $this->assertTrue($bicim->ikinciSatirGerekli('ml', 'su_bardagi'));

        app()->setLocale('tr');
        $this->assertTrue($bicim->ikinciSatirGerekli('ml', 'su_bardagi'));
    }

    /** Canlıda "3 стакана сахарный песок", "3 szklanki cukier", "3 copos açúcar" basılıyordu. */
    public function test_ingredient_line_follows_the_language_order(): void
    {
        $bicim = app(HouseholdMeasurementFormatter::class);

        app()->setLocale('ru');
        $this->assertSame("Сахарный песок \u{2014} 3 стакана", $bicim->yabanciSatir('3 стакана', 'сахарный песок', true));
        $this->assertSame('Соль', $bicim->yabanciSatir('', 'соль', false));

        app()->setLocale('pl');
        $this->assertSame('szt.', $bicim->measureLabel($this->birim('adet'), '2'));
        $this->assertSame("Jajka \u{2013} 2 szt.", $bicim->yabanciSatir('2 szt.', 'jajka', true));

        app()->setLocale('pt');
        $this->assertSame('3 copos de açúcar', $bicim->yabanciSatir('3 copos', 'açúcar', true));
        $this->assertSame('3 ovos', $bicim->yabanciSatir('3', 'ovos', false));
        $this->assertSame('maço', $bicim->measureLabel($this->birim('demet'), '1'));

        app()->setLocale('de');
        $this->assertSame('1 TL Natron', $bicim->yabanciSatir('1 TL', 'Natron', true));
        // Canlıda "½ Wasserglas Ungesalzene Erdnüsse" basılıyordu.
        $this->assertSame('½ Wasserglas ungesalzene Erdnüsse', $bicim->yabanciSatir('½ Wasserglas', 'Ungesalzene Erdnüsse', true));
        $this->assertSame('2 grüne Paprika', $bicim->yabanciSatir('2', 'Grüne Paprika', false));
        $this->assertSame('1 TL schwarzer Pfeffer', $bicim->yabanciSatir('1 TL', 'Schwarzer Pfeffer', true));
        $this->assertSame('Frische Minze', $bicim->yabanciSatir('', 'Frische Minze', false));
        $this->assertSame('2 EL Tomatenmark', $bicim->yabanciSatir('2 EL', 'Tomatenmark', true));
        $this->assertSame('2 geröstete rote Paprika', $bicim->yabanciSatir('2', 'Geröstete rote Paprika', false));
        $this->assertSame('1 frisch gepresster Zitronensaft', $bicim->yabanciSatir('1', 'Frisch gepresster Zitronensaft', false));
    }

    /** Canlıda Almanca "1 Eier", "2 Quitte", Portekizce "1 ovos", "4 pão lavash" basılıyordu. */
    public function test_countable_ingredient_agrees_with_the_number(): void
    {
        $bicim = app(HouseholdMeasurementFormatter::class);

        app()->setLocale('de');
        $this->assertSame('Ei', $bicim->adSayiyla('Eier', '1', ''));
        $this->assertSame('Quitten', $bicim->adSayiyla('Quitte', '2', ''));
        $this->assertSame('Zwiebeln', $bicim->adSayiyla('Zwiebel', '1.5', ''));
        $this->assertSame('Zwiebel', $bicim->adSayiyla('Zwiebel', '0.5', ''));
        $this->assertSame('Ei vom Bauernhof', $bicim->adSayiyla('Eier vom Bauernhof', '1', ''));
        $this->assertSame('Frisches Yufka-Teigblatt', $bicim->adSayiyla('Frische Yufka-Teigblätter', '1', ''));
        $this->assertSame('Tırnak-Pide (türkisches Fladenbrot)', $bicim->adSayiyla('Tırnak-Pide (türkisches Fladenbrot)', '3', ''));
        $this->assertSame('Eier', $bicim->adSayiyla('Eier', '1', 'EL'));

        app()->setLocale('pt');
        $this->assertSame('ovo', $bicim->adSayiyla('ovos', '1', ''));
        $this->assertSame('pães lavash', $bicim->adSayiyla('pão lavash', '4', ''));
        $this->assertSame('ovo caipira', $bicim->adSayiyla('ovos caipiras', '1', ''));
        $this->assertSame('suco de limão-siciliano fresco', $bicim->adSayiyla('suco de limão-siciliano fresco', '2', ''));

        app()->setLocale('ru');
        $this->assertSame('яйца', $bicim->adSayiyla('яйца', '1', ''));
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

    /** Canlıda Portekizce not mastardı: "1 cebola, picar", "3 tomates, ralar". */
    public function test_portuguese_note_agrees_with_the_ingredient(): void
    {
        $bicim = app(HouseholdMeasurementFormatter::class);

        app()->setLocale('pt');
        $this->assertSame('picada', $bicim->hazirlik('doğranmış', 'cebola'));
        $this->assertSame('ralados', $bicim->hazirlik('rendelenmiş', 'tomates'));
        $this->assertSame('quebradas grosseiramente', $bicim->hazirlik('iri dövülmüş', 'nozes sem casca'));
        $this->assertSame('bem picados', $bicim->hazirlik('ince doğranmış', 'pimentões verdes compridos'));
        $this->assertSame('espremido', $bicim->hazirlik('suyu sıkılmış', 'limão-siciliano'));
        $this->assertSame('derretida', $bicim->hazirlik('eritilmiş', 'manteiga'));
        $this->assertSame('limpa', $bicim->hazirlik('ayıklanmış', 'vagem'));
        $this->assertSame('em cubos', $bicim->hazirlik('küp doğranmış', 'tomates'));

        foreach (require lang_path('pt/hazirlik.php') as $karsilik) {
            $this->assertDoesNotMatchRegularExpression('/\b(?:picar|ralar|cortar|frigorífico|sumo|cozer|barrar)\b/u', $karsilik);
        }

        app()->setLocale('de');
        $this->assertSame('gehackt', $bicim->hazirlik('doğranmış', 'Zwiebel'));
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

    /**
     * 12 Ekim 2026'da canlıda: Almanca adım rozeti "⏱ 10 Timer starten"
     * basıyordu (step_timer TR/EN'de süre, diğer dillerde eylemdi) ve beş
     * dilde pişirme modu etiketleri ters çevrilmişti.
     */
    public function test_timer_and_cook_mode_labels_mean_the_same_in_every_locale(): void
    {
        foreach (['tr', 'en', 'de', 'fa', 'pl', 'pt', 'ru'] as $dil) {
            $satirlar = json_decode((string) file_get_contents(lang_path($dil.'.json')), true);

            $this->assertStringContainsString(':sure', $satirlar['recipe.timer.start'], $dil);
            $this->assertArrayNotHasKey('recipe.show.step_timer', $satirlar, $dil);
            $this->assertNotSame($satirlar['recipe.show.cook_mode_on'], $satirlar['recipe.show.cook_mode_off'], $dil);
            $this->assertDoesNotMatchRegularExpression('/[\x{1F300}-\x{1FAFF}\x{23F1}]/u', $satirlar['recipe.show.cook_mode_off'], $dil);
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
