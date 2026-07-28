# UI/UX Audit

## Güçlü yönler

- Public V3 hiyerarşisi, Türkçe CTA’lar, recipe detail household ölçekleme ve güven bağlamı anlaşılır.
- Pantry empty state ve canonical-confidence açıklaması somut bir sonraki adım veriyor.
- Admin sidebar, breadcrumb, filter/table shells ve data-quality empty state düzenli bir tasarım sistemi gösteriyor.

## Bulgular

1. **P1 — Admin dark contrast:** Koyu dashboard screenshot’ında ana başlık, metrikler ve açıklamalar yüzeyden ayrışmıyor. Editörün uzun süre kullanımı için blocker niteliğinde.
2. **P2 — Placeholder-first katalog:** V3 card/detay yapısı güçlü olsa da 50 tarifin tamamında temsili SVG kullanımı güven/premium hissini kırıyor.
3. **P2 — Home motion first paint:** Hero h1/p kısa süre opacity 0; JS/animation koşullarında ana değer önerisi gecikiyor.
4. **P3 — Bilgi mimarisi:** Footer’daki bilgi bağlantıları bilgi sayfasına değil tarif listesine gidiyor.
5. **P3 — Form ergonomisi:** Büyük admin formlarında tutarlı dirty-state/sticky save standardı görünür değil.

## Önerilen UX ilkesi

Önce okunabilirlik ve veri güveni; sonra hareket. Renk, chart ve badge hiçbir zaman tek durum göstergesi olmamalı; risk/boşluk metinle de açıklanmalı.
