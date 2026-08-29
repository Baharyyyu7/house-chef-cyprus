<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

/**
 * .avif dosyaları doğru MIME türüyle servis edilmeli.
 *
 * Sunucu `.avif` uzantısını tanımıyordu ve dosyaları `text/plain` olarak
 * veriyordu. Dosyaların kendisi sağlamdı (`ftypavif` imzası, doğru boyut,
 * HTTP 200) ama `<picture>` içinde tarayıcı `type="image/avif"` diyerek
 * o kaynağı seçiyor, `text/plain` alıyor ve çözümleyemiyor: `load` olayı
 * ateşleniyor ama `naturalWidth` sıfır kalıyor. Kırık medya koruması da
 * bunu hata sayıp görseli gri yer tutucuyla değiştiriyordu.
 *
 * Anasayfanın hero görseli üretimde tam olarak bu yüzden yer tutucuya
 * düşmüştü. `ExpiresByType image/avif` kuralı zaten vardı ama `AddType`
 * hiç tanımlanmamıştı, yani sunucuya uzantının hangi tür olduğu
 * söylenmiyordu.
 */
final class AvifIsServedWithTheRightMimeTypeTest extends TestCase
{
    public function test_the_web_root_declares_the_avif_mime_type(): void
    {
        $this->assertStringContainsString('AddType image/avif .avif', $this->htaccess());
    }

    public function test_the_declaration_is_guarded_by_the_mime_module(): void
    {
        // Modül yoksa AddType sunucuyu 500'e düşürür.
        $kaynak = $this->htaccess();

        $this->assertMatchesRegularExpression(
            '/<IfModule mod_mime\.c>.*?AddType image\/avif \.avif.*?<\/IfModule>/s',
            $kaynak
        );
    }

    public function test_the_expiry_rule_still_matches_a_declared_type(): void
    {
        // ExpiresByType image/avif kuralı vardı ama tür hiç tanımlı
        // olmadığı için hiçbir dosyaya uygulanmıyordu.
        $kaynak = $this->htaccess();

        $this->assertStringContainsString('ExpiresByType image/avif', $kaynak);
        $this->assertStringContainsString('AddType image/avif', $kaynak);
    }

    private function htaccess(): string
    {
        $yol = public_path('.htaccess');

        $this->assertFileExists($yol);

        return (string) file_get_contents($yol);
    }
}
