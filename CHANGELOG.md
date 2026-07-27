# Changelog

## Visual Design Phase 1

- Koyu forest, cream ve gold token'ları ile merkezi House Chef Cyprus tasarım sistemi eklendi.
- Özgün SVG logo/favikon, public ve admin navigasyonu, mobil drawer, tema desteği ve erişilebilir odak stilleri uygulandı.
- Ana sayfa, tarifler, detay, eşleştirme, pantry, favoriler, hesap ve kimlik doğrulama ekranları responsive olarak yenilendi.

## Final Product Core Audit

- Livewire/Alpine CSP regresyonu kapatıldı; lokal gerçek giriş akışı yeniden çalışır durumda doğrulandı.
- Kullanıcıya özel Favorites listesi, görünür pantry düzenleme ve tarif görünürlüğü korumaları tamamlandı.
- System Events listesi gerçek detay ekranına bağlandı; kullanılmayan Favorite modeli kaldırıldı.
- Ürün çekirdeği kontrollü olarak Visual Design aşamasına hazır kabul edildi.

## Foundation E — Final Audit

- API v1 success/error envelope, 16 endpoint OpenAPI 3.1 coverage ve sözleşme kontrol komutu tamamlandı.
- CI kalite kapısı; iki aşamalı seed, cache denetimi, audit/tarama ve local clean-install smoke testiyle genişletildi.
- Environment validation, yerel süreç scriptleri, governance şablonları ve taşınabilirlik denetimi eklendi.

## Foundation A–D

- Recipe/Nutrition/Data Quality, cache/queue/scheduler/performance, security/media ve observability/backup/restore altyapıları tamamlandı.
