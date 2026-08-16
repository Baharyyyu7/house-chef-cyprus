<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\BlogPost;
use RuntimeException;

/**
 * 2026-08-16'da gövdesinde "veritabanı testi için sistem tarafından otomatik
 * oluşturulmuştur" yazan ~3000 sayfa yayına alındı ve sitemap'e girdi. Bu, arama
 * motorları için scaled content abuse desenidir ve cezası alan adı geneline
 * uygulanır. Kendini test verisi ilan eden bir kaydın yayınlanması engellenir.
 */
final class BlogPostObserver
{
    /** Üretilen test kayıtlarının gövdesinde bulunan imzalar. */
    private const TEST_MARKERS = [
        'Test verisi',
        'veritabanı testi',
        'sistem tarafından otomatik oluşturulmuştur',
    ];

    public function saving(BlogPost $post): void
    {
        if ($post->status !== 'published') {
            return;
        }

        $body = (string) $post->body;

        foreach (self::TEST_MARKERS as $marker) {
            if (mb_stripos($body, $marker) !== false) {
                throw new RuntimeException(
                    'BLOG_TEST_CONTENT_CANNOT_BE_PUBLISHED: "'.$post->slug.'" gövdesinde test verisi imzası taşıyor ("'.$marker.'"). Yayınlamadan önce gerçek içerikle değiştirin.'
                );
            }
        }
    }
}
