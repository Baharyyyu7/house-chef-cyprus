# Full Site Route Audit

**Envanter:** 153 route; 71 GET/HEAD, 63 POST, 10 PUT, 8 DELETE, 1 PUT/PATCH.  
**Kanıt:** `php artisan route:list --json`, controller/view incelemesi, yerel HTTP ve temsilî browser QA. Parametreli mutasyon route’ları yalnız sözleşme/middleware açısından incelendi; veri yazılmadı.

## Route grupları

| Grup | Erişim | Middleware | Durum | Audit notu |
|---|---|---|---|---|
| `/`, `/tarifler`, `/tarifler/{slug}` | Guest | web | HTTP 200 | Public published+verified guard var; list/detail V3 mevcut. |
| `/login`, `/register`, password/reset/verify | Guest/auth | web, throttle | HTTP 200 doğrulandı | Auth Blade/Livewire yüzeyleri mevcut; gerçek e-posta teslimi audit dışı. |
| `/dashboard`, `/profile` | Member | auth (+ dashboard verified) | Controller/View mevcut | Profile Livewire, tema tercihi ve hesap aksiyonları mevcut. |
| `/dolabim`, `/favoriler`, `/ne-pisirebilirim` | Member | auth, verified | Browser pantry empty state doğrulandı | Ownership/403 Feature testleri mevcut. |
| `/pisir/{session}` + step/timer aksiyonları | Member | auth, verified | Route sözleşmesi mevcut | Ownership testleri mevcut; gerçek member browser QA fixture eksik. |
| `/yonetim/*` | Admin | auth, verified, `can:access-admin` | Admin browser doğrulandı | 403 Feature testleri yoğun; admin shell V3. |
| `/api/v1/*` | Public API | api, throttle:api | health HTTP 200 | 16 aktif API endpoint; public IDs ve error contract testleri mevcut. |
| `/health`, `/up`, `/api/v1/{health,status,version}` | Public ops | web/api throttle | HTTP 200 | Sağlık uçları erişilebilir. |

## Public page inventory

| URL | Başlık | Light/Dark | Responsive | State | UX / priority |
|---|---|---|---|---|---|
| `/` | House Chef Cyprus | Var / var | 375 ve 1440 taşmasız | Veri dolu | Hero reveal başlangıçta gizli; P2. |
| `/tarifler` | Tarifler | Var / var | 1440 incelendi | Pagination/empty filtre yolu | 50 kartın görseli placeholder; P1/P2. |
| `/tarifler/{slug}` | Tarif adı | Var / var | 375 taşmasız | ingredients/nutrition/allergen | Household metinleri iyi, hero görsel placeholder; P2. |
| `/login`,`/register`,`/forgot-password` | Türkçe auth | Var / var | Kod/view var | validation/rate limit | Browser login gerçek veri değiştirmeden yeniden gönderilmedi; P3 QA. |
| `/bilinmeyen-sayfa` | 404 | framework | HTTP 404 | error | Güvenli 404 yanıtı; görsel error QA ayrı backlog. |

## Member inventory

| URL | Form/table | Empty state | Authorization | Not |
|---|---|---|---|---|
| `/dolabim` | Add/edit/delete form, cards | Evet, browser görüldü | auth+verified | Canonical confidence metni anlaşılır. |
| `/favoriler` | list/filter/pagination | Evet | auth+verified | Published/verified sorgu korunmalı. |
| `/ne-pisirebilirim` | filters/results | Evet | auth+verified | Ortak search servisi kullanılıyor; büyüyen katalogda in-memory filtre riski. |
| `/pisir/{session}` | steps/timers | session dependent | ownership testli | Mobile cooking QA gerçek member fixture bekliyor. |
| `/profile` | profile/password/delete | form | auth | Livewire V3 surface mevcut. |

## Admin inventory

| Alan | Ana route | Tablo/form | Mobil fallback | Durum |
|---|---|---|---|---|
| Dashboard | `/yonetim` | cards/charts/actions | drawer/shell | Dark contrast P1. |
| Recipes/verification/steps | `/yonetim/tarifler*`, `/icerik-dogrulama*` | CRUD/forms/tabs | responsive tables | kapsamlı, sticky-save/dirty polish P3. |
| Ingredients/units/conversions | `/malzemeler*`, `/olculer*`, `/malzeme-donusumleri*` | tables/forms | `hc-table-wrap` | low-confidence governance P2. |
| Nutrition/allergen | `/beslenme*` | facts/snapshots/relations/forms | tables | freshness/coverage operationalization P2. |
| Data Quality | `/veri-kalitesi*` | filters/detail/bulk | table wrap | skorlama kapsamı P1. |
| Media | `/medya*` | list/upload/detail | table/card | placeholder governance P1/P2. |
| Queue/backups/events/restore | ilgili `/yonetim/*` | list/detail/actions | table wrap | health language P2. |

## Route-contract observations

- Public recipe/API resource contracts public ULID kullanır; mevcut testler internal ID sızıntısı için kapsama sahiptir.
- Yönetim form POST payload’larında Eloquent integer binding kullanımı var; kullanıcıya görünür ID sızıntısı gözlenmedi, fakat admin internal protocol ile public/API contract ayrımı belgelenmeli.
- All admin routes merkezi authorization gate arkasındadır. Member gerçek browser session’ı seed’de bulunmadığından HTTP Feature test kanıtı kullanıldı.
- Mevcut footer’daki “Beslenme bilgisi” ve “Alerjen farkındalığı” `/tarifler`e gider; ayrı bilgi route’ları henüz yok.
