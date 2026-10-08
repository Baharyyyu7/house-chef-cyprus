# İş akışları neden kapalı?

`CI` ve `SEO Static Audit` iş akışları **elle devre dışı bırakıldı**
(23 Eylül 2026).

## Sebep

Depo private, yani GitHub Actions dakikaları ücretli. Hesabın
faturalandırması kapalı olduğu için hiçbir iş başlamıyor; her çalışma
2-4 saniyede şu not ile düşüyor:

> The job was not started because recent account payments have failed or
> your spending limit needs to be increased.

Bu bir kod kusuru değil. Kapatılmadan önce arka arkaya onlarca çalışma
aynı şekilde kırmızıya düştü ve her push'ta yanlış bir "CI başarısız"
sinyali üretti. Sürekli kırmızı duran bir kapı, gerçek bir kusuru da
görünmez yapar.

`Autonomous Growth Engine Heartbeat` (scheduler-ping.yml) de aynı sebeple
hiç çalışmadı ve zamanlayıcı anahtarını açık metin taşıyordu; 8 Ekim
2026'da silindi. Zamanlayıcı üretimde Cloudflare Worker üzerinden dakikada
bir tetikleniyor. Anahtar git geçmişinde kaldığı için sunucuda ve Worker'da
yenilenmeli.

## Gerçek kapı nerede?

Kod kalitesi iki yerde ölçülüyor ve ikisi de çalışıyor:

1. **`scripts/release.sh`** — her yayından önce tam test paketini koşar
   (bugün itibarıyla 1543 test). Testler kırmızıysa yayın çıkmaz.
2. **`php artisan app:invariants`** — üretim *verisinde* tekrar etmemesi
   gereken durumları her sabah ve her yayında ölçer. Test paketinin
   göremediği bozulmalar buradan yakalanır.

## Yeniden açmak için

Faturalandırma düzeldiğinde:

```bash
gh workflow enable "CI" --repo Baharyyyu7/house-chef-cyprus
gh workflow enable "SEO Static Audit" --repo Baharyyyu7/house-chef-cyprus
```

Açmadan önce bir çalışmanın gerçekten başladığını doğrulayın: iş
2-4 saniyede biterse fatura engeli hâlâ duruyordur.
