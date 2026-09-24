<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Media;
use RuntimeException;

/**
 * 2026-08-16 09:14'te tek seferde 2891 medya satırı "approved_real_media" ve
 * "verified" olarak işaretlendi. Satırların hiçbirinde dosya yoktu: path boştu,
 * görsel doğrudan Unsplash CDN'inden hotlink ediliyordu, 2891 satır yalnızca 23
 * farklı fotoğrafa işaret ediyordu ve attribution alanı bu fotoğrafları
 * "House Chef Cyprus Gastronomy" çekmiş gibi gösteriyordu.
 *
 * Hotlink'in kendisi yasak değildir: UnsplashCandidateMediaService bir editör
 * onayıyla hotlink'i yayına alabilir ve bu tasarlanmış bir akıştır. O akışı
 * sahte partiden ayıran şey, onayı veren kullanıcıdır -- toplu yazımda
 * doldurulamayan tek alan odur. Dolayısıyla kapı şunu arar:
 *
 *   - dosyası bizde olmayan (hotlink) bir görsel onaylıysa, onaylayan bir
 *     kullanıcı kaydı bulunmalı;
 *   - dosyası bizde olan bir görsel onaylıysa, path'i dolu olmalı.
 *
 * Lisans alanı burada aranmaz: meşru içe aktarma akışı da bazı durumlarda onu
 * boş bırakıyor ve alan sahte partiyi ayırt etmiyor.
 */
final class MediaObserver
{
    public function saving(Media $media): void
    {
        if ($media->media_status !== 'approved_real_media') {
            return;
        }

        if ($media->kind === 'external_hotlinked_media') {
            if ($media->approved_by_user_id === null) {
                throw new RuntimeException(
                    'MEDIA_HOTLINK_NEEDS_HUMAN_APPROVAL: '.$this->tanim($media).' dosyası bizde olmadan onaylı işaretlenmiş ve onaylayan kullanıcı yok. Hotlink yalnızca editör onayıyla yayına alınabilir.'
                );
            }

            return;
        }

        if (trim((string) $media->path) === '') {
            throw new RuntimeException(
                'MEDIA_APPROVED_WITHOUT_FILE: '.$this->tanim($media).' onaylı işaretlenmiş ama path boş.'
            );
        }
    }

    private function tanim(Media $media): string
    {
        return trim(($media->mediable_type ?? 'medya').'#'.($media->mediable_id ?? '?'));
    }
}
