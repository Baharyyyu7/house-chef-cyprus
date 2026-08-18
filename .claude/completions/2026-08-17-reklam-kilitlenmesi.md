# Reklam kilitlenmesi — çözüldü, açılması sende

**Tarih:** 2026-08-17

## Sorun

Reklamlar hiç çıkmıyordu ve sebebi yapılandırma değil, bir kilitlenmeydi.

Google'ın sertifikalı CMP'si (Privacy & messaging), AdSense betiğinin
içinden gelir. `google-integrations.blade.php` o betiği yalnız
`hc_advertising_consent === 'granted'` iken yüklüyordu:

    onay yok -> AdSense betiği yüklenmez
             -> Google'ın onay mesajı hiç görünmez
             -> sertifikalı onay hiç oluşmaz
             -> onay yok

AdSense panelinde ne seçilirse seçilsin bu döngü kırılmıyordu.

## Yapılan

`integrations.google.adsense.cmp_provider` eklendi:

- `custom` (varsayılan): kendi bandımız sorar, hc_advertising_consent karar
  verir. Bugüne kadarki davranış; hiçbir şey değişmedi.
- `google`: Google'ın kendi CMP'si sorar. AdSense betiği onaydan ÖNCE
  yüklenir, ama Consent Mode v2 varsayılanları `denied` olarak başlar ve
  reklam etiketi TCF sinyali gelene kadar kişiselleştirilmiş istek atmaz.

CSP'ye `fundingchoicesmessages.google.com` ve `tpc.googlesyndication.com`
eklendi — bunlar olmadan onay mesajı sessizce engellenirdi, yani ikinci bir
görünmez tıkanma olurdu. Bu alan adları yalnız `google` modunda açılır.

Kilitlenmenin geri gelmemesi için:
`tests/Feature/GoogleCmpCanActuallyReachTheVisitorTest.php`

## Açmak için (sırayla)

1. AdSense > Gizlilik ve mezajlaşma > Avrupa düzenlemeleri:
   "Google'ın CMP'sini kullan" seçilir ve AEA mesajı YAYINA alınır.
   ("Alternatif sertifikalı CMP" seçili kaldığı sürece çalışmaz.)
2. Sunucudaki `.env`:

       GOOGLE_ADSENSE_ENABLED=true
       GOOGLE_ADSENSE_CLIENT_ID=ca-pub-1614195335767159
       GOOGLE_CERTIFIED_CMP_ENABLED=true
       GOOGLE_ADSENSE_CMP=google

3. `php artisan config:cache`
4. Reklam birimi kimlikleri `config/monetization.php` altındaki `slots`
   dizisine girilir. Yer tutucu kimlikle istek atmak, incelemedeki bir
   hesabın reddedilme yollarından biridir; kod bunu zaten engelliyor ama
   gerçek kimlikler girilmeden reklam basılmaz.

## Hâlâ açık

`config/monetization.php` iki özellik vaat ediyor ve ikisinin de arkasında
bir rota veya servis bulunamadı:

- "Haftalık Otomatik Menü Planlama"
- "Akıllı Alışveriş Listesi Oluşturma"

Bunlar fiyatlandırma sayfasında satılıyor. Ya yapılmalı ya listeden
çıkarılmalı; olmayan özelliği satmak hem tüketici hakları hem AdSense
politikası tarafında risk.
