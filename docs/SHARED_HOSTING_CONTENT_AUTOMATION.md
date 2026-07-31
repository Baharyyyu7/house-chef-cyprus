# Paylaşımlı hosting içerik otomasyonu

Database queue/cache ile çalışır. Cron örneği: her dakika `schedule:run`, her beş dakikada `queue:work --stop-when-empty --tries=2 --timeout=90`. İçerik görevleri küçük batch, scheduler lock ve idempotent slug anahtarları kullanır. Public yayın job'u planlanmamıştır.
