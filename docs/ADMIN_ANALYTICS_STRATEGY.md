# Admin Analytics Strategy

## İlke: gerçek veri veya açık empty state

Admin dashboard hiçbir zaman demo/sahte trend üretmez. Metrik yoksa zaman aralığı, veri eksikliği nedeni ve sonraki ölçüm adımı açıklanan empty state gösterilir. Aggregate sorgular service katmanından gelir, kısa TTL cache ile korunur ve public/admin yetki sınırlarını aşmaz.

## Mevcut kapasite ve boşluk

Mevcut modeller: `SystemMetric`, `SystemEvent`, `AuditLog`, `DataQualityIssue`, `RestoreHistory`, failed jobs, scheduler/queue/cache/health servisleri. Dashboard temel özet ve sistem health sağlayabiliyor; dönemsel chart data contract, metric aggregation endpoint/service, trend history ve responsive chart bileşeni yok. Recipe/favorite/matching/cooking görüntülenme trendi için event toplama kapsamı tanımlı değil; bu yüzden grafik gösterilmez.

## Dashboard metrik sözleşmesi

| Alan | Gerçek kaynak | İlk görünüm | Boşluk / guardrail |
| --- | --- | --- | --- |
| Tarif/kullanıcı/malzeme sayıları | Eloquent aggregate | Anlık kart | Published/draft ayrımı korunur |
| Nutrition/allergen eksikliği | Data Quality aggregate | Kart + kategori | Scan zamanı görünür |
| Quality score | `DataQualityService` | Kart + kategori grafiği | Stale scan uyarısı |
| Failed jobs/queue/scheduler/health | Foundation B/C/D servisleri | Status kartları | Hassas payload yok |
| Backup/restore | Backup/RestoreHistory | Son durum + trend | Dosya/secret sızmaz |
| Tarif ekleme/kayıt trendi | created_at aggregate | 24h/7d/30d/90d chart | Tarih filtresi, timezone |
| Favori/matching/cooking | Audit/system metrics yalnız event varsa | Chart veya empty | Yeni event üretmeden veri uydurma |
| Request/error/media trendi | SystemMetric/SystemEvent aggregate | Chart veya empty | Kısa TTL cache |

## Grafik altyapısı önerisi

Faz 7'de Blade/Alpine uyumlu, lisansı uygun ve hafif bir SVG/canvas chart kütüphanesi seçilir. Seçimden önce bundle boyutu, keyboard tooltip, aria label, dark tokens, reduced-motion, print/export ve CSP uyumu ölçülür. Grafik veri endpointi yalnız admin policy'si arkasında aggregate JSON döndürür; raw audit metadata, IP, token ve kullanıcı içeriği döndürmez.

Her chart bileşeni: 24 saat/7 gün/30 gün/90 gün seçici, loading skeleton, accessible summary table, empty state, tooltip, legend ve reduced-motion statik başlangıç durumuna sahip olur.

## Admin redesign paketleri

1. **Shell:** icon sistemi, aktif nav, desktop/mobile drawer, global filters/toasts.
2. **Dashboard:** gerçek summary + dönemsel chart containerları.
3. **Recipe admin:** completeness/status/nutrition/allergen/media filtresi; wizard ve preview.
4. **Ingredient 360°:** aliases, substitutions, nutrition, allergens, household conversions, density, storage, verification ve source.
5. **Operations:** Data Quality, Failed Jobs, System Status, Events, Backups/Restore için aynı table/detail/action sözleşmesi.

## Test ve güvenlik

- Admin yalnız erişim, member 403, period validation ve empty-state testleri.
- Aggregate query count/performance ve cache invalidation testleri.
- Chart API response'da internal ID/secret/raw payload olmaması.
- Dark/light/reduced-motion/keyboard browser QA.

## Kapanış kriteri

Grafik yalnız metric kaynağı gerçek, aggregate/cached, erişilebilir ve empty durumda dürüst olduğunda yayına alınır.
