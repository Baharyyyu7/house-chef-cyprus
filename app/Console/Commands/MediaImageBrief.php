<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Media;
use App\Models\Recipe;
use Illuminate\Console\Command;

/**
 * Görselleri başka bir araç üretecekse ne üreteceğini kesin bilmesi gerekir:
 * hangi yemek, hangi dosya adı, hangi çözünürlük. Bu komut o brief'i üretir.
 *
 * Dosya adı kuralı ApprovedMediaImportService'ten gelir: "recipe--{slug}.png"
 * dışındaki her ad MEDIA_SOURCE_SLUG_MISMATCH ile reddedilir.
 */
final class MediaImageBrief extends Command
{
    protected $signature = 'media:image-brief
        {--limit=0 : Kaç tarif listelensin, 0 = tümü}
        {--format=markdown : markdown veya json}
        {--out= : Çıktıyı dosyaya yaz}';

    protected $description = 'Görseli eksik veya başka tarifle paylaşılan yayındaki tarifler için üretim brief\'i çıkarır.';

    /** Kaynak bu ölçünün altındaysa içe aktarma SOURCE_TOO_SMALL ile reddedilir. */
    private const MIN_WIDTH = 1400;

    private const MIN_HEIGHT = 850;

    /** Tüm varyantları (hero 1600x1000, portrait 1080x1350) upscale etmeden karşılar. */
    private const TARGET_WIDTH = 1600;

    private const TARGET_HEIGHT = 1350;

    public function handle(): int
    {
        $paylasilanYollar = Media::query()
            ->where('media_status', 'approved_real_media')
            ->where('mediable_type', Recipe::class)
            ->whereNotNull('path')
            ->select('path')
            ->groupBy('path')
            ->havingRaw('count(*) > 1')
            ->pluck('path');

        $tarifler = Recipe::query()
            ->where('status', 'published')
            ->with(['cuisine', 'category', 'ingredients', 'media'])
            ->orderBy('title')
            ->get()
            ->map(function (Recipe $recipe) use ($paylasilanYollar): ?array {
                if (! self::gercekTarif((string) $recipe->title)) {
                    return null;
                }

                $onayli = $recipe->media->firstWhere('media_status', 'approved_real_media');

                if ($onayli === null) {
                    $sebep = 'görsel yok';
                } elseif ($paylasilanYollar->contains($onayli->path)) {
                    $sebep = 'görsel başka tariflerle paylaşılıyor';
                } else {
                    return null;
                }

                return [
                    'slug' => $recipe->slug,
                    'title' => $recipe->title,
                    'reason' => $sebep,
                    'cuisine' => $recipe->cuisine?->name,
                    'category' => $recipe->category?->name,
                    'ingredients' => $recipe->ingredients->take(6)->pluck('name')->all(),
                    'filename' => 'recipe--'.$recipe->slug.'.png',
                ];
            })
            ->filter()
            ->values();

        $limit = (int) $this->option('limit');
        if ($limit > 0) {
            $tarifler = $tarifler->take($limit)->values();
        }

        $cikti = $this->option('format') === 'json'
            ? (string) json_encode(['spec' => $this->spec(), 'count' => $tarifler->count(), 'recipes' => $tarifler->all()], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
            : $this->markdown($tarifler->all());

        $hedef = (string) ($this->option('out') ?? '');
        if ($hedef !== '') {
            file_put_contents($hedef, $cikti);
            $this->info('Brief yazıldı: '.$hedef.' ('.$tarifler->count().' tarif)');

            return self::SUCCESS;
        }

        $this->line($cikti);

        return self::SUCCESS;
    }

    /**
     * Yayındaki 678 tarifin 450'si "{Mutfak} Usulü Limonlu {Malzeme}" kalıbıyla
     * toplu üretilmiş, 17'sinin başlığı sıfatta kesilmiş ("Peynirli", "Fırında").
     * Bunlar için görsel ürettirmek anlamsızdır: üretilecek fotoğrafın
     * göstereceği bir yemek yoktur ve maliyeti boşa gider. Brief yalnızca adı
     * gerçek bir yemeği tarif eden tarifleri listeler.
     */
    public static function gercekTarif(string $baslik): bool
    {
        $baslik = trim($baslik);

        if ($baslik === '' || mb_strpos($baslik, 'Usulü Limonlu') !== false) {
            return false;
        }

        $kelimeler = preg_split('/\s+/u', $baslik) ?: [];

        if (count($kelimeler) > 1) {
            return true;
        }

        // Tek kelimelik başlık "Menemen" ya da "Humus" olabilir; bunlar geçerli
        // yemek adlarıdır. Geçersiz olan, ardındaki ismi kaybetmiş sıfatlardır.
        $parcalar = ['Fırın', 'Fırında', 'Et', 'Tavuk', 'Konya', 'Şiş', 'Dürüm'];

        return ! in_array($baslik, $parcalar, true)
            && preg_match('/(lı|li|lu|lü)$/u', $baslik) !== 1;
    }

    /** @return array<string, mixed> */
    private function spec(): array
    {
        return [
            'filename_pattern' => 'recipe--{slug}.png',
            'upload_directory' => 'storage/app/media-import/recipes/',
            'format' => 'PNG veya JPEG',
            'minimum' => self::MIN_WIDTH.'x'.self::MIN_HEIGHT,
            'recommended' => self::TARGET_WIDTH.'x'.self::TARGET_HEIGHT,
            'notes' => 'Dosya adı tarifin slug\'ıyla birebir eşleşmeli. Görsel o yemeği göstermeli; genel mutfak fotoğrafı kabul edilmez.',
        ];
    }

    /** @param list<array{slug:string,title:string,reason:string,cuisine:?string,category:?string,ingredients:array<int,string>,filename:string}> $tarifler */
    private function markdown(array $tarifler): string
    {
        $s = "# Görsel üretim brief'i — House Chef Cyprus\n\n";
        $s .= "## Teknik gereksinimler\n\n";
        $s .= '- **Dosya adı**: `recipe--{slug}.png` — slug ile birebir eşleşmeli, aksi hâlde içe aktarma reddedilir.'."\n";
        $s .= '- **Çözünürlük**: en az '.self::MIN_WIDTH.'×'.self::MIN_HEIGHT.', önerilen **'.self::TARGET_WIDTH.'×'.self::TARGET_HEIGHT.'**.'."\n";
        $s .= "- **Neden bu ölçü**: hero 1600×1000, portrait 1080×1350, square 1080×1080 varyantları büyütme yapılmadan üretilebilsin diye.\n";
        $s .= "- **Format**: PNG veya JPEG.\n";
        $s .= "- **Teslim yeri**: `storage/app/media-import/recipes/`\n\n";
        $s .= "## İçerik kuralı\n\n";
        $s .= "Görsel **o yemeği** göstermeli. Genel mutfak, malzeme yığını veya soyut görsel kabul edilmez; bu tür görseller kalite denetiminde düşürülür.\n\n";
        $s .= '## Üretilecek görseller ('.count($tarifler)." adet)\n\n";
        $s .= "| # | Yemek | Dosya adı | Mutfak / Kategori | Ana malzemeler | Sebep |\n";
        $s .= "| --- | --- | --- | --- | --- | --- |\n";

        foreach ($tarifler as $i => $t) {
            $s .= sprintf(
                "| %d | %s | `%s` | %s | %s | %s |\n",
                $i + 1,
                $t['title'],
                $t['filename'],
                trim(($t['cuisine'] ?? '—').' / '.($t['category'] ?? '—')),
                $t['ingredients'] === [] ? '—' : implode(', ', $t['ingredients']),
                $t['reason']
            );
        }

        return $s;
    }
}
