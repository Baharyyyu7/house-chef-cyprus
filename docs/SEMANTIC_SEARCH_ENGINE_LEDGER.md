# Semantic Search Engine ledger

## Başlangıç durumu

| Task | Modül | Durum | Kanıt | Sonraki güvenli eylem |
| --- | --- | --- | --- | --- |
| SS-001 | Public recipe eligibility | `AUDITED` | Mevcut sorgu `published` + `verified` ile sınırlandırılmış | Ineligible-content regression testleri çalıştır |
| SS-002 | Türkçe query normalization | `CODE_COMPLETE` | Local deterministic normalizer | PHP 8.4+ ortamında unit test |
| SS-003 | Intent parsing | `CODE_COMPLETE` | Zaman, yöntem, kahvaltı/Kıbrıs, safe exclusion ve ingredient tokenları | Gerçek katalogta browser QA |
| SS-004 | Weighted ranking / autocomplete | `IN_PROGRESS` | Harici sağlayıcı gerektirmez | Gerçek sıralama ölçümü ve admin diagnostics |
| SS-005 | Alias dictionary governance | `BLOCKED_EDITORIAL` | Mevcut alias kayıtları var, bölgesel belirsizlikte otomatik merge yasak | Onaylı alias kaynakları |
| SS-006 | Production verification | `BLOCKED_PRODUCTION_ACCESS` | SSH/browser production oturumu yok | Aktif release ve canlı QA |

## Uygulanan güvenli sözleşme

- Natural-language query parser yalnız deterministic işaretler üretir: dahil/hariç malzemeler, süre, yöntem, kahvaltı/Kıbrıs bağlamı ve kolaylık.
- `yumurtasız`, `sütsüz` gibi ifadeler yalnız listelenen malzemeye göre dışlama filtresidir; alerjen güvenliği veya dietary suitability iddiası değildir.
- Query normalizasyonu saklanan public içeriği değiştirmez.
- Recipe search bütün sorgu yollarında `published` ve `verified` sınırını korur; draft, private revision, admin ve member verileri indekslenmez.
- Harici embedding veya AI provider çağrısı yapılmaz, kişisel arama sorgusu üçüncü taraflara gönderilmez.

## Bilinen sınırlar

Bu ilk local arama katmanı MySQL/Hostinger üzerinde production benchmark, FULLTEXT kararı, autocomplete, typo önerisi ve browser QA olmadan `PRODUCTION_VERIFIED` sayılamaz. Public katalogtaki gerçek medya/editoryal eksikler ayrıca arama uygunluğu veya güvenlik anlamına gelmez.
