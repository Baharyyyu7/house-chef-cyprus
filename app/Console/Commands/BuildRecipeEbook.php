<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\DigitalProduct;
use App\Models\Recipe;
use App\Services\Measurements\RecipeIngredientDisplayService;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Yayındaki tariflerden satılabilir bir e-kitap üretir.
 *
 * Mağaza altyapısı hazırdı ama satılacak tek bir ürün yoktu: sayfa açılıyor,
 * içi boş görünüyordu. Kitap uydurma içerikten değil, sitede zaten yayında ve
 * doğrulanmış tariflerden derlenir - okur ne aldığını görebilir.
 *
 * Dosya `local` diskine yazılır, yani web kökünden erişilemez. İndirme
 * yalnız satın alma sonrası üretilen imzalı jetonla yapılır.
 */
final class BuildRecipeEbook extends Command
{
    protected $signature = 'shop:build-ebook
        {--limit=30 : Kitaba kaç tarif girsin}
        {--price=149.00 : Satış fiyatı}
        {--slug=kibris-mutfagi-secmeler : Ürün adresi}
        {--force : Ürün varsa dosyayı ve kaydı yenile}';

    protected $description = 'Yayındaki doğrulanmış tariflerden PDF e-kitap üretir ve mağaza ürününü oluşturur.';

    public function handle(RecipeIngredientDisplayService $olculer): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $slug = (string) $this->option('slug');

        $tarifler = Recipe::query()
            ->where('status', 'published')
            ->where('verification_status', 'verified')
            ->with(['cuisine', 'category', 'recipeIngredients.ingredient', 'recipeIngredients.group', 'steps'])
            ->orderBy('title')
            ->limit($limit)
            ->get();

        if ($tarifler->isEmpty()) {
            $this->error('Yayında doğrulanmış tarif yok; kitap üretilmedi.');

            return self::FAILURE;
        }

        // Adımı olmayan bir tarif kitapta yarım kalır: okur parasını verdiği
        // sayfada eksik içerik görmemeli.
        $tarifler = $tarifler->filter(fn (Recipe $tarif): bool => $tarif->steps->isNotEmpty() && $tarif->recipeIngredients->isNotEmpty())->values();

        if ($tarifler->isEmpty()) {
            $this->error('Tariflerin malzeme veya adım bilgisi eksik; kitap üretilmedi.');

            return self::FAILURE;
        }

        $html = view('shop.ebook', [
            'tarifler' => $tarifler,
            'olculer' => $olculer,
            'uretimTarihi' => now(),
        ])->render();

        $options = new Options;
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('a4');
        $dompdf->render();

        $yol = 'products/'.$slug.'.pdf';
        Storage::disk('local')->put($yol, (string) $dompdf->output());

        $sayfa = max(1, $dompdf->getCanvas()->get_page_count());

        $urun = DigitalProduct::query()->firstOrNew(['slug' => $slug]);

        if ($urun->exists && ! $this->option('force')) {
            $urun->forceFill(['file_path' => $yol, 'page_count' => $sayfa])->save();
            $this->info('Ürün vardı; yalnız dosya ve sayfa sayısı güncellendi.');
        } else {
            $urun->forceFill([
                'title' => 'Kıbrıs Mutfağı Seçmeler',
                'slug' => $slug,
                'description' => $tarifler->count().' doğrulanmış tarif; ölçüleri, adımları ve mutfak notlarıyla tek bir PDF\'te. Sitede yayında olan tariflerden derlendi, çevrimdışı okunmak ve mutfakta yanınızda durmak için hazırlandı.',
                'features' => json_encode([
                    $tarifler->count().' tarif, adım adım anlatım',
                    'Ev ölçüleriyle birlikte gram karşılıkları',
                    'Yazdırmaya uygun sayfa düzeni',
                    'Ömür boyu erişim, güncellemeler dahil',
                ], JSON_UNESCAPED_UNICODE),
                'price' => (float) $this->option('price'),
                'currency' => 'TRY',
                'file_path' => $yol,
                'page_count' => $sayfa,
                'is_active' => true,
            ])->save();
        }

        $this->info('Kitap üretildi: '.$yol);
        $this->line('Tarif: '.$tarifler->count().' · Sayfa: '.$sayfa.' · Fiyat: '.number_format((float) $urun->price, 2, ',', '.').' TL');
        $this->line('Boyut: '.number_format(Storage::disk('local')->size($yol) / 1024, 0, ',', '.').' KB');

        return self::SUCCESS;
    }
}
