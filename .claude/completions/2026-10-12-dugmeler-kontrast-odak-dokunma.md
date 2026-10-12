# 2026-10-12 Düğmeler: kontrast, odak halkası, dokunmatik hedef, çift gönderim

Canlı: 8a00332 (tam paket 1856/1856).

## Ölçüm (canlı, önce)
- Birincil turuncu düğmede krem yazı 2,71:1; koyu temada hero CTA beyaz/#fb923c 2,26:1.
- Odak halkası krem zeminde 2,18-2,75:1.
- 375 px: kart düğmeleri 36, başlık 40-42, adım 40, yıldız 24, karusel noktası 24 px.
- 17 düğmede 10 farklı imza (ana sayfa).

## Değişen
- --hc-brand-contrast açıkta #171717; --hc-focus-ring #c2410c / #fb923c.
- @media (pointer: coarse) 44 px; yıldızlar her yerde 44 px alan.
- Ham turuncu/kehribar düğmeler hc-v3-btn--primary; emoji/ok glifi yerine x-ui.icon.
- Adım rozeti kalktı (de "⏱ 10 Timer starten"); recipe.timer.start 7 dil; step_timer silindi.
- 5 dilde ters pişirme modu etiketleri düzeldi.
- SSS kartı tanımsız --hc-surface-card yerine --hc-surface-default.
- app.js: POST çift gönderim kilidi (aria-busy), bfcache'te açılır.

## Doğrulama
- Canlı 1024 px açık/koyu: 0 kontrast hatası. 375 px dokunmatik: 0 küçük hedef.
- Ölçüm tuzağı: gizli panelde CSS geçişi donar; transition:none ile ölçüldü.

## Kalan
- Hareket katmanında taşan eğriler; eski hc-btn ailesi ayrı kural; admin opportunities tanımsız token.
