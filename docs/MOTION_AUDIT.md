# Motion Audit

- Reveal, hover, drawer transition, card interaction ve `prefers-reduced-motion` azaltımı kodda mevcut.
- `v2-reveal` `.55s` + delay’lerde başlangıçta `opacity:0` uygular. Browser’da hero h1/p ilk screenshotta gizli, ~1.2 saniye sonra görünür oldu.
- **AUD-008 (P2):** Motion progressive enhancement olmalı: başlangıç görünür, yalnız geliştirilmiş ortamda enter transition uygulanmalı.
- **AUD-009 (P3):** Chart hareketleri için metinsel özet tek kaynak olmalı; animasyon tamamlanmadan önemli bilgi saklanmamalı.
- Kapanış QA: reduced-motion, klavye ile drawer, modal focus restore, 375/1440 visual regression.
