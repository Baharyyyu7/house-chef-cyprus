<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Recipe;
use Illuminate\Console\Command;

/**
 * Yayındaki 678 tarifin 463'ünün adı bir yemeği tarif etmiyor: "Akdeniz
 * Mutfağı Usulü Limonlu" gibi yarım kalmış kalıplar ya da "Peynirli" gibi
 * ismini kaybetmiş sıfatlar. Hepsi seoMetadata'da noindex taşıyor, yani
 * Google zaten göstermiyor -- ama siteyi gezen bir insan görüyor ve AdSense
 * incelemesi de görüyor. "Düşük değerli içerik" gerekçesiyle reddedilen
 * başvuruların tipik deseni budur.
 *
 * Kayıt silinmez, yalnız taslağa çekilir: içerik dursun, yayında durmasın.
 * Geri almak tek bir status güncellemesidir.
 */
final class UnpublishTemplateRecipes extends Command
{
    protected $signature = 'recipes:unpublish-templates
        {--apply : Değişikliği yaz, aksi hâlde yalnız rapor}
        {--limit=0 : Kaç kayıt işlensin, 0 = tümü}';

    protected $description = 'Adı bir yemeği tarif etmeyen yayındaki tarifleri taslağa çeker.';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $limit = (int) $this->option('limit');

        $hedefler = Recipe::query()
            ->where('status', 'published')
            ->orderBy('id')
            ->get(['id', 'title', 'slug'])
            ->reject(fn (Recipe $recipe): bool => Recipe::titleDescribesADish((string) $recipe->title))
            ->values();

        if ($limit > 0) {
            $hedefler = $hedefler->take($limit)->values();
        }

        $kalan = Recipe::query()->where('status', 'published')->count() - $hedefler->count();

        $this->line(sprintf('  yayında kalacak : %d', $kalan));
        $this->line(sprintf('  taslağa çekilecek: %d', $hedefler->count()));
        $this->newLine();
        $this->line('  örnekler:');

        foreach ($hedefler->take(6) as $recipe) {
            $this->line(sprintf('    - %s', $recipe->title));
        }

        if ($hedefler->isEmpty()) {
            $this->info('Taslağa çekilecek tarif yok.');

            return self::SUCCESS;
        }

        if (! $apply) {
            $this->newLine();
            $this->comment('Uygulamak için --apply ekleyin. Kayıtlar silinmez, yalnız taslağa çekilir.');

            return self::SUCCESS;
        }

        $sayac = 0;

        foreach ($hedefler as $recipe) {
            // published_at korunur: geri alındığında özgün yayın tarihi kaybolmasın.
            Recipe::query()->whereKey($recipe->id)->update(['status' => 'draft']);
            $sayac++;
        }

        $this->newLine();
        $this->info(sprintf('%d tarif taslağa çekildi. Yayında %d tarif kaldı.', $sayac, $kalan));

        return self::SUCCESS;
    }
}
