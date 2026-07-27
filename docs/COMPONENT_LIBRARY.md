# Component Library V3

## Mevcut primitive'ler

| Primitive | Kullanım | Varyantlar |
| --- | --- | --- |
| `x-ui.button` | Buton ve link aksiyonu | primary, secondary, tertiary, ghost, outline, danger, success; sm/md/lg; loading/icon/full width |
| `x-ui.input` | Form input shell | default/error/disabled/readonly |
| `x-ui.card` | Surface/card shell | base, interactive, selected, danger |
| `x-ui.badge` | Status/chip | neutral, accent, success, warning, danger, info |
| `x-ui.alert` | Inline feedback | info, success, warning, danger |
| `x-ui.empty-state` | Dürüst boş durum | icon/title/CTA slot |
| `x-ui.chart-shell` | Gelecek grafik kabı | erişilebilir empty state |
| `x-ui.icon` | Tutarlı SVG icon | 14/16/20/24/32 px |

## Form erişilebilirliği

`x-input-label` ve `x-input-error` V3 field contract'ına taşındı. Her form label `for`/input `id` ilişkisinin, hata mesajı `role=alert` erişilebilirliğinin ve Türkçe validation metninin korunması gerekir. Browser autofill, date/number controls ve %200 zoom pilot QA'da kontrol edilir.

## Table ve feedback

`hc-v3-table-shell` kontrollü yatay taşma, `hc-v3-table` başlık/hücre ve semantic border contract'ı sağlar. Büyük tablolar mobile card fallback'e geçmeden önce ekran gereksinimiyle değerlendirilir. Toast/modal/drawer mevcut Alpine davranışını korur; V3 yalnız görünüm/focus tokenlarını standardize eder.

