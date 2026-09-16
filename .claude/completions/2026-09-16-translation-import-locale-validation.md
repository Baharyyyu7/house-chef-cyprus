# Çeviri aktarımında dil eşleşmesi doğrulaması

Tarih: 2026-09-16

## Tamamlanan değişiklik

`content:import-translations`, JSON içindeki `locale` alanını kayıt aramasından ve yazma işleminden önce denetliyor. Dil bilgisi eksik, metin türünde olmayan veya boş dosyalar reddediliyor. Dosyanın dili teslim klasörünün diliyle birebir eşleşmediğinde dosya atlanıyor ve nedeni raporlanıyor. Kontrol hem rapor hem uygulama modunda geçerli.

Yanlış dil ve eksik dil için iki regresyon testi eklendi. Önce her iki test de beklenen 0 yerine 3 çeviri kaydı oluştuğunu göstererek başarısız oldu; düzeltmeden sonra geçti. Tarif gövdesi testindeki örnek dosyaya `locale: en` eklenerek mevcut gövde kontrolleri korundu.

## Doğrulama

- `php artisan test --filter='Locale|Translat|GlobalLanguage|EveryOpenLocale|Hreflang'`: 108 test, 450 doğrulama, tamamı başarılı. Son koşu: 2858 ms.
- Değişen üç PHP dosyası için `pint --test`: başarılı.
- `git diff --check`: başarılı.
- Bağımsız kod incelemesi: düzeltme gerektiren bulgu yok; inceleyen ajan testleri yeniden çalıştırmadı.
- `storage`: 365 MB; deploy paketi yok. Test çeviri teslim klasörü temizlendi.

## Teslim durumu

Düzeltme yerel çalışma alanında. Canlıya dağıtım veya içerik aktarımı yapılmadı. Dil etkinleştirme, içerik yayınlama ve medya onayı yapılmadı.
