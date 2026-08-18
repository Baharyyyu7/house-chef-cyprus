<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Media;
use App\Models\User;
use App\Services\Media\PhotographicContentAnalyzer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * İçe aktarılmış tarif görsellerine insan onayını kaydeder.
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
 * Üçünü de geçmeyen kayıt atlanır ve sebebi yazılır. Onaylayan kullanıcı
 * kayda işlenir; "kim onayladı" sorusu her zaman cevaplanabilir kalır.
 */
final class ApproveVerifiedRecipeImagery extends Command
{
    protected $signature = 'media:approve-verified
        {--apply : Onayı yaz, aksi hâlde yalnız rapor}
        {--reviewer= : Onayı üstlenen kullanıcının e-postası}
        {--limit=0 : Güvenli batch sınırı, 0 = tümü}';

    protected $description = 'Kimlik ve fotoğraf kontrolünden geçmiş tarif görsellerine insan onayını kaydeder.';

    public function handle(PhotographicContentAnalyzer $analiz): int
    {
        $eposta = (string) $this->option('reviewer');

        if ($eposta === '') {
            $this->error('--reviewer zorunlu: onayın kime ait olduğu kayda geçmeli.');

            return self::FAILURE;
        }

        $onaylayan = User::query()->where('email', $eposta)->first();

        if ($onaylayan === null || ! $onaylayan->isAdmin()) {
            $this->error("Yönetici bulunamadı: {$eposta}");

            return self::FAILURE;
        }

        $uygula = (bool) $this->option('apply');
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

            if ($uygula) {
                $medya->forceFill([
                    'approved_by_user_id' => $onaylayan->id,
                    'approved_at' => now(),
                ])->save();
            }
        }

        $etiket = $uygula ? '[apply]' : '[dry-run]';
        $this->line("{$etiket} onaylanan: {$onaylanan}");

        foreach ($atlanan as $sebep => $adet) {
            $this->line("  atlandı ({$sebep}): {$adet}");
        }

        if (! $uygula) {
            $this->line('Yazmak için --apply ekleyin.');
        }

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
