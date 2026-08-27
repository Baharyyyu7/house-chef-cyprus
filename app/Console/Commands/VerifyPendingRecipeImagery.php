<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Media;
use App\Services\Media\PhotographicContentAnalyzer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * İçe aktarılmış tarif görsellerini onaydan ÖNCE makineyle doğrular.
 *
 * Bu onay kapısı boşuna yok: bir kez 2891 sahte "onaylı" medya satırı
 * yayına çıktı ve bunların bir kısmı yemek fotoğrafı bile değildi. O
 * yüzden burada hiçbir şey körlemesine onaylanmaz; her kayıt üç bağımsız
 * kontrolü geçmek zorunda ve üçü de burada tekrar sınanır:
 *
 *   1. Kimlik eşleşmesi güçlü (fidelity_status = strong_match)
 *   2. Dosya gerçekten diskte ve okunabiliyor
 *   3. Fotoğraf testinden geçiyor (düz grafik, illüstrasyon, yer tutucu değil)
 *
 * Bu komut hiçbir şeyi onaylamaz ve veritabanına yazmaz. İlk hâli onay
 * yarısını yazıyordu ve bu bir kuyruğu bozdu: yönetim ekranı onay
 * bekleyenleri approved_by_user_id boş olanlar diye buluyor, kapı ise ayrıca
 * inceleyen alanını istiyor. Yarısı yazılınca kayıtlar ikisinin arasında
 * kaldı - kapı geçirmiyor, ekran da artık göstermiyordu.
 *
 * Onayı insan verir, Medya ekranından. Bu komutun işi o insana neyin
 * sınandığını söylemek: hangi görseller üç kontrolü de geçti, hangileri
 * neden geçmedi.
 */
final class VerifyPendingRecipeImagery extends Command
{
    protected $signature = 'media:verify-pending
        {--limit=0 : Güvenli batch sınırı, 0 = tümü}';

    protected $description = 'Onay bekleyen tarif görsellerini kimlik, dosya ve fotoğraf kontrolünden geçirip raporlar.';

    public function handle(PhotographicContentAnalyzer $analiz): int
    {
        $limit = (int) $this->option('limit');

        $sorgu = Media::query()
            ->where('media_status', 'approved_real_media')
            ->whereNull('approved_by_user_id')
            ->whereNotNull('path');

        if ($limit > 0) {
            $sorgu->limit($limit);
        }

        $onaylanan = 0;
        $atlanan = [];

        foreach ($sorgu->get() as $medya) {
            $sebep = $this->neden($medya, $analiz);

            if ($sebep !== null) {
                $atlanan[$sebep] = ($atlanan[$sebep] ?? 0) + 1;

                continue;
            }

            $onaylanan++;
            $baslik = $medya->mediable->getAttribute('title');
            $this->line('  onaya hazır: '.(is_string($baslik) && $baslik !== '' ? $baslik : $medya->public_id));
        }

        $this->line("Üç kontrolü de geçen: {$onaylanan}");

        foreach ($atlanan as $sebep => $adet) {
            $this->line("  geçmeyen ({$sebep}): {$adet}");
        }

        $this->line('Onay Medya ekranından verilir: /yonetim/medya');

        return self::SUCCESS;
    }

    private function neden(Media $medya, PhotographicContentAnalyzer $analiz): ?string
    {
        if ($medya->fidelity_status !== 'strong_match') {
            return 'kimlik_eslesmesi_zayif';
        }

        $diskAdi = $medya->disk ?: 'public';

        if (! Storage::disk($diskAdi)->exists((string) $medya->path)) {
            return 'dosya_yok';
        }

        $sonuc = $analiz->analyze($diskAdi, (string) $medya->path);

        if (($sonuc['photographic'] ?? false) !== true) {
            return 'fotograf_degil';
        }

        return null;
    }
}
