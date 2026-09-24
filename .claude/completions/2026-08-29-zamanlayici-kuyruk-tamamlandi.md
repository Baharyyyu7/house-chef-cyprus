# Zamanlayıcı ve kuyruk - 2026-08-29

Hostinger'ın cron servisi 2026-08-02'den beri ölüydü. Zamanlayıcı
Cloudflare Worker + `/_zamanlayici` ucu ile ayağa kalktı, ama kuyruk
kalkmadı. Bu belge kuyruğun neden kalkmadığını ve nasıl çözüldüğünü
kaydediyor.

## Kök neden

**Üretimde HTTP bağlamında Artisan konsol komutları kayıtlı değil.**

```
production.ERROR: The command "queue:drain-bounded" does not exist.
CommandNotFoundException
```

Aynı komut, aynı sürümde, aynı sunucuda CLI'da sorunsuz listeleniyor ve
çalışıyor. Dosya yerinde. Yerelde `artisan serve` altında da çalışıyor.
Yani sebep Hostinger'ın web ortamına özgü.

Bu tek bulgu bütün belirtileri açıklıyor:

- `queue_drain` zamanlanmış görevi CLI'da koşuyor, uçtan koşmuyordu -
  içinde `Artisan::call` var
- Hata görünmüyordu - istisna zamanlayıcının içinde yutuluyordu
- `queue:work` de çalışmıyordu - o da bir Artisan çağrısı
- Sağlık raporu "healthy" demeye devam ediyordu

## Çözüm

Mantık `App\Services\Queue\BoundedQueueDrainer` servisine taşındı; uç onu
doğrudan çağırıyor, konsol çekirdeği hiç devreye girmiyor. Konsol komutu
servisin ince sarmalayıcısı olarak duruyor.

## Yol boyunca çıkan diğer hatalar

1. **Sağlık eşiği yalan söylüyordu.** Rapor `max_age_minutes: 30` diyor,
   durum hesabı kodda sabit 26 saat kullanıyordu. Zamanlayıcı bir gün ölü
   kalsa "healthy" görünürdü - ölümü 26 gün fark ettirmeyen sinyal buydu.
2. **Kuyruk zamanlamaya bağlı değildi.** Ayrı bir cron kaydıyla koşuyordu.
3. **Yanlış kuyruğu işliyordu.** Bekleyen işler `media` kuyruğundaydı,
   `queue:work` varsayılan olarak yalnız `default` işler.
4. **WorkerOptions sleep.** Varsayılan 3 saniyeyle işçi boş her kuyrukta
   uyuyor; dört kuyruk için çağrı başına 12 saniye.
5. **Küçük görsel işi yer tutucuları atlamıyordu.** Yer tutucular SVG;
   üretici MIME listesinde olmadıkları için MEDIA_SOURCE_UNAVAILABLE
   atıyor, iş üç kez denenip kalıcı düşüyordu.
6. **Artefakt kirli çalışma ağacından paketleniyordu.** Üretim, başka bir
   ajanın commit edilmemiş `AiKitchenService.php` sürümünü çalıştırır hale
   gelmişti. Script artık kirli ağaçta duruyor; düzeltme temiz bir
   `git worktree`'den yeniden üretilip deploy edildi.

## Doğrulama

- İş 04:07:59'da kuyruğa atıldı, hiçbir müdahale olmadan 04:09:40'ta
  işlenmişti. Worker → uç → kuyruk zinciri uçtan uca çalışıyor.
- HTTP tetiği: 204, kuyruk 2 → 0, başarısız iş yok.
- `app:queue-health`: pending 0, failed 0, stuck 0, healthy.
- `app:scheduler-health`: son çalışma sürekli bir dakikadan taze.
- Uç noktalar: `/`, `/tarifler`, `/blog`, `/malzemeler`, `/health` → 200.
- SEO denetimi: 1 açık sorun, skor 90.
- Test paketi: 800/800.

## Açık kalan

Hostinger'ın cron servisi hâlâ bozuk; Worker etrafından dolaşıyor. Destek
kaydı açılmadı - bu sahibinin işi.

`shared/logs/scheduler.log` 2 Ağustos'ta donmuş görünmeye devam edecek ve
bu normal: o dosyayı eski cron komutunun çıktı yönlendirmesi yazıyordu.
Doğru kontrol noktası `app:scheduler-health`.
