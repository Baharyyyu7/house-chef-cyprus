# Tarif medyası ve Search Console doğrulaması

## Tamamlananlar

- Tarif listeleme sorgularında medya uygunluk denetiminin gerektirdiği alanlar
  eksiksiz yükleniyor; onaylı görseller artık yer tutucuya dönüşmüyor.
- Altı eksik tarif görseli kimlik eşleşmesiyle teslim edildi ve
  `SYNTHETIC_APPROVED` kayıtları olarak içe alındı.
- `google715e7aec24977e0f.html` kalıcı public doğrulama dosyası eklendi.
- Fiziksel Hostinger web kökü eşitlemesi Google doğrulama dosyalarını da
  koruyor.
- PHPStan tarafında medya ilişkisi, çeviri ilişkisi ve manifest
  koleksiyonu tipleri gerçek modellerine daraltıldı.
- Depodaki PHP dosyaları Pint standardına getirildi.

## Doğrulama

- PHPUnit: 750 test, 4564 assertion başarılı.
- Odaklı medya/Search Console regresyonu: 15 test, 48 assertion başarılı.
- Pint: başarılı.
- PHPStan: 0 hata.
- Vite production build: başarılı.
- Secret, forbidden-file ve doküman bağlantı taramaları: başarılı.
- Deploy ve canlı HTTP/DOM kontrolleri final raporunda ayrıca kaydedilir.
