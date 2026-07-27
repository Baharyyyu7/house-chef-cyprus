# Yerel geliştirme

`./setup.sh` bağımlılıkları yükler, SQLite veritabanını oluşturur ve demo veriyi seed eder. `./start.sh` Laravel, queue worker, scheduler ve Vite süreçlerini PID dosyalarıyla yönetir. `./stop.sh` yalnızca bu PID dosyalarındaki canlı süreçleri durdurur.

Windows PowerShell eşleri depodadır; gerçek Windows doğrulaması henüz yapılmamıştır. PowerShell’de PHP, Composer ve Node kurulu olmalıdır.

`./scripts/clean-install-test.sh`, geçici bir klasörde yalnızca takip edilen dosyalardan sıfırdan kurulum yapar; ana çalışma klasörüne yazmaz.
