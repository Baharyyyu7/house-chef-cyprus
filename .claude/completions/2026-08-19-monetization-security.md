# Monetization güvenlik kapısı ve Stripe webhook doğrulaması

**Tarih:** 2026-08-19

## Tamamlananlar

- `PAYMENTS_ENABLED=false` varsayılan güvenlik kapısı eklendi; eski
  `MONETIZATION_PAYMENTS_ENABLED` anahtarı geriye uyumlu fallback olarak
  korundu.
- Abonelik, dijital ürün ve destek checkout rotaları ödeme altyapısı hazır
  değilken kayıt veya yetki üretmeden aynı güvenli uyarıyla geri dönüyor.
- Fiyatlandırma çağrıları ödeme kapalıyken `Yakında Aktif` olarak devre dışı.
- Mevcut Stripe imza doğrulaması, idempotent webhook kayıtları ve ödeme
  doğrulamalı premium modeli korundu.
- `tests/Feature/MonetizationTest.php` içinde beş zorunlu güvenlik senaryosu
  eklendi.

## Doğrulama

- Odaklı monetization paketi: 39 test, 140 assertion — geçti.
- Yeni güvenlik paketi: 5 test, 24 assertion — geçti.
- Tüm PHPUnit paketi: 663 test, 4310 assertion — geçti.
- Değişen ödeme dosyalarında Pint — geçti.
- Değişen ödeme kapsamındaki PHPStan — geçti.
- `git diff --check` — geçti.

Tam Pint ve tam PHPStan denetiminde bu görev dışındaki eşzamanlı çalışma
dosyalarında bulgular görüldü; ödeme kapsamındaki dosyalarda bulgu yoktur.
