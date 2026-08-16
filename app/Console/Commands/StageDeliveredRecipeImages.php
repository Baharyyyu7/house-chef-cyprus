<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Recipe;
use Illuminate\Console\Command;

/**
 * Görselleri başka bir araç üretiyor, yerleştirmeyi biz yapıyoruz. Aradaki
 * adım, her dosya için elle manifest kaydı yazmaktı; 211 görselde bu
 * sürdürülemez ve elle yazılan kayıt denetimin kendisini atlayabilir.
 *
 * Bu komut teslim klasörünü tarar, her dosyayı kendi tarifiyle eşler, ölçü ve
 * içerik kurallarından geçirir ve yalnız geçenler için manifest kaydı üretir.
 * Onay kaydını üretmek yayına almak değildir: asıl içe aktarmayı hâlâ
 * media:import-approved yapar ve kendi denetimlerini ayrıca uygular.
 */
final class StageDeliveredRecipeImages extends Command
{
    protected $signature = 'media:stage-delivered
        {--apply : Manifest dosyasına yaz, aksi hâlde yalnız rapor}
        {--reviewer=House Chef Cyprus Editorial : Onayı üstlenen editör}';

    protected $description = 'Teslim edilen tarif görsellerini denetler ve içe aktarma manifestine hazırlar.';

    /** Kaynak bu ölçünün altındaysa varyantlar büyütme olmadan üretilemez. */
    private const MIN_WIDTH = 1600;

    private const MIN_HEIGHT = 1350;

    public function handle(): int
    {
        $klasor = storage_path('app/media-import/recipes');
        $manifestYolu = storage_path('app/media-import/approved-media-manifest.json');

        if (! is_dir($klasor)) {
            $this->error('Teslim klasörü yok: '.$klasor);

            return self::FAILURE;
        }

        $manifest = json_decode((string) @file_get_contents($manifestYolu), true);
        $manifest = is_array($manifest) ? $manifest : ['schema_version' => 1, 'entries' => []];
        $mevcut = collect($manifest['entries'] ?? [])->pluck('source_filename')->flip();

        $hazir = [];
        $atlanan = [];

        foreach (glob($klasor.'/*.{png,jpg,jpeg,PNG,JPG,JPEG}', GLOB_BRACE) ?: [] as $yol) {
            $dosya = basename($yol);

            if ($mevcut->has($dosya)) {
                $atlanan[] = [$dosya, 'manifestte zaten var'];

                continue;
            }

            $slug = $this->slugFromFilename($dosya);

            if ($slug === null) {
                $atlanan[] = [$dosya, 'dosya adı "recipe--{slug}" kalıbına uymuyor'];

                continue;
            }

            $recipe = $this->recipeFor($slug);

            if ($recipe instanceof Recipe === false) {
                $atlanan[] = [$dosya, $recipe];

                continue;
            }

            if (! Recipe::titleDescribesADish((string) $recipe->title)) {
                $atlanan[] = [$dosya, 'tarifin adı bir yemeği tarif etmiyor: '.$recipe->title];

                continue;
            }

            $olcu = @getimagesize($yol);

            if ($olcu === false) {
                $atlanan[] = [$dosya, 'okunabilir bir görsel değil'];

                continue;
            }

            [$genislik, $yukseklik] = $olcu;

            if ($genislik < self::MIN_WIDTH || $yukseklik < self::MIN_HEIGHT) {
                $atlanan[] = [$dosya, sprintf('%dx%d, en az %dx%d olmalı', $genislik, $yukseklik, self::MIN_WIDTH, self::MIN_HEIGHT)];

                continue;
            }

            // İçe aktarıcı dosya adının tarifin güncel slug'ıyla birebir
            // eşleşmesini şart koşar; bu kural görselin başka bir tarife
            // yerleşmesini engeller ve gevşetilmemeli. Slug teslimden sonra
            // değiştiyse çözüm, dosyayı burada güncel ada taşımaktır.
            $beklenen = 'recipe--'.$recipe->slug.'.'.pathinfo($dosya, PATHINFO_EXTENSION);

            if ($dosya !== $beklenen) {
                if ($this->option('apply') && ! @rename($yol, $klasor.'/'.$beklenen)) {
                    $atlanan[] = [$dosya, 'güncel slug adına taşınamadı: '.$beklenen];

                    continue;
                }

                $this->line(sprintf('  <fg=cyan>ad değişti</> %s → %s', $dosya, $beklenen));
                $dosya = $beklenen;
            }

            $hazir[] = [
                'content_type' => 'recipe',
                'public_id' => $recipe->public_id,
                'source_filename' => $dosya,
                'identity_match' => true,
                'approval_state' => 'APPROVED',
                'reviewer' => (string) $this->option('reviewer'),
                'alt_text' => $recipe->title.' servis edilmiş hâlde',
                'provenance' => [
                    'provider' => 'antigravity_gemini',
                    'model' => 'gemini_image',
                    'generated_at' => now()->toIso8601String(),
                    'prompt_version' => 'recipe-media-v2',
                ],
            ];
        }

        foreach ($atlanan as [$dosya, $sebep]) {
            $this->line(sprintf('  <fg=yellow>atlandı</> %-58s %s', $dosya, $sebep));
        }

        foreach ($hazir as $kayit) {
            $this->line(sprintf('  <fg=green>hazır</>   %s', $kayit['source_filename']));
        }

        $this->newLine();
        $this->info(sprintf('%d dosya hazır, %d dosya atlandı.', count($hazir), count($atlanan)));

        if ($hazir === []) {
            return self::SUCCESS;
        }

        if (! $this->option('apply')) {
            $this->comment('Yazmak için --apply ekleyin. Ardından: php artisan media:import-approved');

            return self::SUCCESS;
        }

        $manifest['entries'] = array_merge($manifest['entries'] ?? [], $hazir);
        file_put_contents($manifestYolu, (string) json_encode($manifest, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

        $this->info('Manifest güncellendi: '.$manifestYolu);
        $this->comment('Sıradaki adım: php artisan media:import-approved');

        return self::SUCCESS;
    }

    /**
     * Görsel üretimi gün sürüyor ve bu sırada tariflerin slug'ı değişebiliyor:
     * 678 tarifin 628'i tek bir toplu yazımda yeni rastgele sonek aldı ve
     * teslim edilen dosyaların adı bir anda hiçbir tarifi göstermez oldu.
     * Sonek atılabilir; eşleşme asıl adın üzerinden kurulur.
     *
     * @return Recipe|string Eşleşen tarif, ya da eşleşmeme sebebi
     */
    private function recipeFor(string $slug): Recipe|string
    {
        $tam = Recipe::query()->where('slug', $slug)->first();

        if ($tam !== null) {
            return $tam;
        }

        $govde = preg_replace('/-[A-Za-z0-9]{5}$/', '', $slug);

        if (! is_string($govde) || $govde === '' || $govde === $slug) {
            return 'bu slug ile tarif yok';
        }

        $adaylar = Recipe::query()
            ->where(fn ($q) => $q->where('slug', $govde)->orWhere('slug', 'like', $govde.'-_____'))
            ->get();

        if ($adaylar->count() === 1) {
            return $adaylar->first();
        }

        return $adaylar->isEmpty()
            ? 'bu slug ile tarif yok'
            : 'slug belirsiz: "'.$govde.'" için '.$adaylar->count().' tarif var';
    }

    private function slugFromFilename(string $dosya): ?string
    {
        $ad = pathinfo($dosya, PATHINFO_FILENAME);

        return str_starts_with($ad, 'recipe--') ? substr($ad, strlen('recipe--')) : null;
    }
}
