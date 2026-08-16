<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Media;
use Illuminate\Console\Command;

/**
 * MediaObserver yeni satırların dosyasız "onaylı" yazılmasını engelliyor, ama
 * kapı kurulmadan önce yazılmış 2891 satır yerinde duruyor ve zararı somut:
 * bu satırlar sıralamada önce geldiği için gerçek fotoğrafı gölgeliyorlar.
 * Adana Kebabı'nın fotoğrafı içeri alındıktan sonra bile tarif sayfası
 * "onaylı görsel yok" yer tutucusunu gösteriyordu.
 *
 * Komut aynı kuralı mevcut veriye uygular. Silme yapmaz: yalnız media_status
 * alanını no_media'ya çeker, satır ve kaynak bilgisi durur. fidelity_status'e
 * dokunulmaz; o alan "görsel bu yemeğe ait mi" sorusunu yanıtlar ve başka
 * akışlara aittir.
 */
final class DemoteUnverifiableMedia extends Command
{
    protected $signature = 'media:demote-unverifiable {--apply : Değişikliği yaz, aksi hâlde yalnız rapor}';

    protected $description = 'Dosyası ve insan onayı olmayan "onaylı" medya kayıtlarını no_media durumuna çeker.';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');

        $dosyasiz = Media::query()
            ->where('media_status', 'approved_real_media')
            ->where('kind', '!=', 'external_hotlinked_media')
            ->where(fn ($q) => $q->whereNull('path')->orWhere('path', ''))
            ->get();

        $onaysizHotlink = Media::query()
            ->where('media_status', 'approved_real_media')
            ->where('kind', 'external_hotlinked_media')
            ->whereNull('approved_by_user_id')
            ->get();

        $this->line(sprintf('  dosyası olmayan onaylı kayıt        : %d', $dosyasiz->count()));
        $this->line(sprintf('  editör onayı olmayan hotlink        : %d', $onaysizHotlink->count()));

        $hedef = $dosyasiz->merge($onaysizHotlink);

        if ($hedef->isEmpty()) {
            $this->info('Düşürülecek kayıt yok.');

            return self::SUCCESS;
        }

        if (! $apply) {
            $this->newLine();
            $this->comment(sprintf('%d kayıt düşürülecek. Uygulamak için --apply ekleyin.', $hedef->count()));

            return self::SUCCESS;
        }

        foreach ($hedef as $media) {
            $media->forceFill(['media_status' => 'no_media'])->saveQuietly();
        }

        $this->info(sprintf('%d kayıt no_media durumuna çekildi.', $hedef->count()));

        return self::SUCCESS;
    }
}
