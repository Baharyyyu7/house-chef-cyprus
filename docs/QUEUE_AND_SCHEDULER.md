# Queue ve Scheduler

## Yerel temel

Uygulama lokal ortamda `database` queue sürücüsüyle çalışır. Domain kodu queue
bağlantısına bağımlı değildir; Redis/Horizon'a geçiş yalnızca yapılandırma ve
işletim katmanında yapılır.

## İş politikası

`RecalculateRecipeNutritionJob`, `nutrition` kuyruğunda çalışır; üç deneme,
120 saniye timeout, 30/120 saniye kontrollü backoff ve tarif bazlı unique lock
kullanır. İş başarılı olduğunda queue heartbeat ve son başarılı iş zamanı
güncellenir. Hata kaydı kullanıcıya teknik ayrıntı sızdırmaz.

## Başarısız işler

`/yonetim/basarisiz-isler` yalnızca yöneticiler içindir. Ham payload, token,
parola, stack trace ve dosya yolu gösterilmez. Tekli/toplu retry ve forget
aksiyonları audit log'a kaydedilir.

## Scheduler

Kayıtlı görevler:

- `data_quality_scan`: günlük veri kalite taraması
- `cache_warm_lookups`: günlük lookup cache ısıtma

Her ikisi isimli, overlap korumalıdır. `SchedulerHealthService`, başlangıç,
tamamlanma, başarısızlık, güvenli hata kodu ve süreyi cache içinde tutar.
Bir görev hatasında durum önce güvenli biçimde kaydedilir, ardından scheduler'a
hata yeniden iletilir.

Lokal doğrulama için `php artisan schedule:list` ve `php artisan schedule:run`
kullanılabilir.

`queue:health` yalnızca gözlem yapar; worker heartbeat'i yapay olarak yenilemez.
Bu nedenle worker yoksa dashboard doğru biçimde unavailable/stale durumunu
gösterebilir.
