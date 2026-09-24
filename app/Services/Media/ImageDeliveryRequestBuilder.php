<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Models\Recipe;

/**
 * Görselleri Antigravity üretiyor, yerleştirmeyi biz yapıyoruz. Aradaki adım
 * bugüne kadar elle hazırlanan bir metin dosyasıydı: prompt bir betikle
 * üretiliyor, masaüstüne yazılıyor ve oradan kopyalanıyordu. Bu sınıf aynı işi
 * panelde yapar ve kuralları tek yerde tutar.
 *
 * RecipeImagePromptBuilder'dan ayrıdır: o, kendi sağlayıcımıza gönderilen ve
 * malzeme doğrulama kapısına bağlı üretim promptunu kurar. Buradaki metin ise
 * dışarıdaki bir araca elle verilen teslim talebidir ve teknik şartı taşır --
 * ilk partide 1200x896 kaynak 1600x1350'ye en-boy korunmadan zorlandı ve her
 * görsel %13 dikey gerildi.
 */
final class ImageDeliveryRequestBuilder
{
    /** Varyantları büyütmeden üretmek için gereken en küçük kaynak. */
    public const TARGET_WIDTH = 1600;

    public const TARGET_HEIGHT = 1350;

    /** Prompt'a girdiğinde görseli bulanıklaştıran, ayırt edici olmayan malzemeler. */
    private const GENERIC = ['ana malzeme', 'tuz', 'karabiber', 'su', 'zeytinyağı', 'sıvı yağ', 'toz şeker'];

    private const STYLE = 'professional overhead food photography, natural daylight from the side, '
        .'shallow depth of field, rustic ceramic plate on a warm stone or linen surface, '
        .'Mediterranean home-kitchen setting, no text, no watermark, no hands, no logos, '
        .'appetising and realistic, not stylised illustration';

    public function filename(Recipe $recipe): string
    {
        return 'recipe--'.$recipe->slug.'.png';
    }

    public function prompt(Recipe $recipe): string
    {
        $parcalar = [sprintf(
            'A single serving of %s, a %s dish',
            $recipe->title,
            $recipe->cuisine?->name ?: 'Mediterranean'
        )];

        if ($recipe->category?->name) {
            $parcalar[] = 'served as '.mb_strtolower((string) $recipe->category->name, 'UTF-8');
        }

        $malzemeler = $this->belirginMalzemeler($recipe);

        if ($malzemeler !== []) {
            $parcalar[] = 'visibly made with '.implode(', ', $malzemeler);
        }

        $parcalar[] = self::STYLE;

        return implode('. ', $parcalar).'.';
    }

    /** Antigravity'ye verilecek, teknik şartı da taşıyan tam talep metni. */
    public function request(Recipe $recipe): string
    {
        return implode("\n", [
            $this->prompt($recipe),
            '',
            'Dosya adı: '.$this->filename($recipe),
            'Çözünürlük: en az '.self::TARGET_WIDTH.'x'.self::TARGET_HEIGHT.' (Gemini çıktısını 2K isteyin)',
            'Ölçekleme: en-boy oranını koruyun, gerekirse ortadan kırpın; germeyin.',
            'Teslim: storage/app/media-import/recipes/',
        ]);
    }

    /** @return list<string> */
    private function belirginMalzemeler(Recipe $recipe): array
    {
        $adlar = $recipe->ingredients
            ->pluck('name')
            ->filter(fn ($ad): bool => is_string($ad) && $ad !== '')
            ->map(fn (string $ad): string => trim($ad));

        $belirgin = $adlar->reject(fn (string $ad): bool => in_array(mb_strtolower($ad, 'UTF-8'), self::GENERIC, true));

        return ($belirgin->isNotEmpty() ? $belirgin->take(4) : $adlar->take(3))->values()->all();
    }
}
