<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Recipe;
use Illuminate\Console\Command;

/**
 * Malzeme listesi hiç doldurulmamış tarifleri yayından çeker.
 *
 * Yayında 27 tarif vardı ve hepsinin malzeme listesi birebir aynıydı:
 * "Ana Malzeme, Zeytinyağı, Kuru Soğan, Sarımsak, Tuz, Karabiber". Baklava,
 * trileçe, turşu, hamsi, kokoreç - hepsi. Bu bir tarif değil, doldurulmamış
 * şablonun kendisi.
 *
 * Başlığa bakan recipes:unpublish-templates bunları yakalayamıyor, çünkü
 * başlıkları gerçek yemek adı. Buradaki ölçüt malzemede: gerçek hiçbir
 * tarifte "Ana Malzeme" adında bir malzeme bulunmaz. Malzemesi hiç olmayan
 * tarif de aynı sepete girer.
 *
 * Yemek sitesinde yanlış malzeme en ağır hatadır: "Karamelli Trileçe"yi
 * yapmaya çalışan biri soğan ve sarımsak görüyordu.
 *
 * Kayıt silinmez, published_at korunur; tarif taslağa çekilir ve malzemesi
 * düzeltildiğinde geri yayınlanabilir.
 */
final class UnpublishPlaceholderIngredientRecipes extends Command
{
    protected $signature = 'recipes:unpublish-placeholder-ingredients
        {--apply : Değişikliği yaz, aksi hâlde yalnız rapor}
        {--limit=0 : Kaç kayıt işlensin, 0 = tümü}';

    protected $description = 'Malzeme listesi doldurulmamış yayındaki tarifleri taslağa çeker.';

    /** Gerçek bir tarifte bulunmayacak yer tutucu malzeme adı. */
    public const YER_TUTUCU = 'ana malzeme';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $limit = (int) $this->option('limit');

        $hedefler = Recipe::query()
            ->where('status', 'published')
            ->with('ingredients:id,name')
            ->orderBy('id')
            ->get()
            ->filter(fn (Recipe $recipe): bool => self::malzemesiDoldurulmamis($recipe))
            ->values();

        if ($limit > 0) {
            $hedefler = $hedefler->take($limit)->values();
        }

        $this->line(sprintf('  taslağa çekilecek: %d', $hedefler->count()));

        foreach ($hedefler->take(8) as $recipe) {
            $this->line(sprintf('    - %s', $recipe->title));
        }

        if ($hedefler->isEmpty()) {
            $this->info('Malzemesi doldurulmamış yayında tarif yok.');

            return self::SUCCESS;
        }

        if (! $apply) {
            $this->newLine();
            $this->line('Yazmak için --apply ekleyin. Kayıt silinmez, yalnız taslağa çekilir.');

            return self::SUCCESS;
        }

        foreach ($hedefler as $recipe) {
            // published_at korunur: malzeme düzeltilince tarif kendi
            // tarihiyle geri yayınlanabilsin.
            $recipe->forceFill(['status' => 'draft'])->save();
        }

        $this->info(sprintf('%d tarif taslağa çekildi.', $hedefler->count()));

        return self::SUCCESS;
    }

    public static function malzemesiDoldurulmamis(Recipe $recipe): bool
    {
        if ($recipe->ingredients->isEmpty()) {
            return true;
        }

        return $recipe->ingredients
            ->pluck('name')
            ->contains(fn ($ad): bool => str_contains(mb_strtolower((string) $ad), self::YER_TUTUCU));
    }
}
