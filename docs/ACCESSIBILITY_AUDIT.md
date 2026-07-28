# Accessibility Audit

## Mevcut kanıt

- Başlık hiyerarşisi, form label’ları, alert `role`, progressbar, mobile drawer dialog semantics, focus ring ve reduced-motion kuralları kodda mevcut.
- Recipe detail fotoğrafları alt text, decorative hero img `aria-hidden` kullanımı ile ayrıştırılmış.

## Açıklar

- **AUD-015 P3:** Skip-link yok; klavye kullanıcısı her sayfada navigasyonu tekrar geçmek zorunda.
- **AUD-009 P2:** Admin chart metin özeti iki kez görünüyor/duyuruluyor.
- Modal, dropdown, error association ve 200% zoom için route bazlı tam QA matrisi eksik.
- Koyu admin contrast WCAG AA açısından P1 riskidir.

## Kapanış kriteri

Keyboard tab order, Escape/focus restore, modal/drawer trap, chart summary, contrast ve zoom senaryoları gerçek tarayıcıda kanıtlanmalı.
