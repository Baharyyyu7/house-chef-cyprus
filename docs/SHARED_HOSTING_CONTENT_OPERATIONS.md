# Paylaşımlı Hosting İçerik Operasyonları

Cron süreçleri kısa ömürlü, idempotent ve database queue uyumludur. Uzun işlemler batch/chunk kullanır; aynı anda çalışan cron işlerini önlemek için mevcut overlap/lock korumaları kullanılır. Otomatik yayınlama kesinlikle kapalıdır.

