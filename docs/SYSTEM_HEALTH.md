# Sistem Sağlığı

## Queue health

`php artisan queue:health` bekleyen, reserved, failed ve stuck işleri; worker
heartbeat, son başarılı/başarısız işi ve genel durumu gösterir.
`php artisan queue:health --json` aynı sözleşmenin makine okunabilir JSON
çıktısını verir.

Stuck iş eşiği `QUEUE_STUCK_AFTER_SECONDS` (varsayılan 300), worker heartbeat
eşiği `QUEUE_HEARTBEAT_STALE_AFTER_SECONDS` (varsayılan 120) ile merkezî olarak
yapılandırılır.

## Yönetim görünümü

Yönetim ana ekranı queue ve scheduler özetini güvenli biçimde gösterir. Failed
Jobs ayrıntısı hata mesajı veya payload yerine yalnızca güvenli job sınıfı,
kimlik, queue/connection ve hata kodu özeti sunar.

## Operasyon sınırı

Bu temel, database queue ile doğrulanmıştır. Redis/Horizon, worker process
yönetimi ve production alarm entegrasyonu sonraki production operasyon fazına
aittir.
