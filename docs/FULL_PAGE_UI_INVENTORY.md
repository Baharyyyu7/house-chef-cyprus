# Full Page & Route UI Inventory

Kaynak: `php artisan route:list --json`, controller/Blade envanteri, 2026-07-27. Route aksiyonları dahil toplam 117 uygulama/API route'u vardır: 16 API, 30 public/member/sistem ve 71 admin route/aksiyonu. Livewire asset route'ları bu iş envanterine dahil değildir.

Durum anahtarı: **İşlev** = çalışır çekirdek / kısmi / planlı; **Görsel** = V2 preview / Phase 1 / Breeze varsayılan / admin taslak; **Durumlar** = E/L/H sırasıyla empty/loading/error.

## Public ve auth ekranları

| Route | Sayfa / rol | Uygulama | Layout | İşlev | Görsel / light-dark-mobile | E/L/H | Form / tablo / grafik / motion | Öncelik |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
| `/` | Ana sayfa / public | `RecipeController@index`, `recipes/index` | `site` | Çalışır | V2 preview; tema/mobile var | E var; L kısmi; H layout alert | Arama; kart reveal/hover | P1 |
| `/tarifler` | Tarif listesi / public | `RecipeController@index`, `recipes/index` | `site` | Çalışır | V2 kontrast düzeltildi; liste redesign bekliyor | E var; L yok; H filtre validation | Filtre formu/pagination; kart motion | P1 |
| `/tarifler/{slug}` | Tarif detay / public | `RecipeController@show`, `recipes/show` | `site` | Çalışır | Phase 1; V3 bekliyor | E kısmi; L yok; 404 var | Favorite/cooking/AI form; timer | P1 |
| `/health` | Sistem health / public | `HealthController` | response | Çalışır | UI değil | JSON health/error | Yok | P3 |
| `/login` | Giriş / guest | Volt `auth/login` | guest | Çalışır | Breeze varsayılan | Validation/error var; L yok | Login formu | P1 |
| `/register` | Kayıt / guest | Volt `auth/register` | guest | Çalışır | Breeze varsayılan | Validation/error var; L yok | Kayıt formu | P1 |
| `/forgot-password` | Şifre sıfırlama isteği / guest | Volt | guest | Çalışır | Breeze varsayılan | Status/error var | Form | P2 |
| `/reset-password/{token}` | Yeni şifre / guest | Volt | guest | Çalışır | Breeze varsayılan | Token/error var | Form | P2 |
| `/verify-email` | E-posta doğrulama / member | Volt | guest | Çalışır | Breeze varsayılan | Status/error var | Form/CTA | P2 |
| `/confirm-password` | Parola onayı / member | Volt | guest | Çalışır | Breeze varsayılan | Error var | Form | P2 |

## Member ekranları

| Route | Sayfa / rol | Uygulama | Layout | İşlev | Görsel / light-dark-mobile | E/L/H | Form / tablo / grafik / motion | Öncelik |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
| `/dashboard` | Hesap dashboard / verified member | View `dashboard` | app/site bileşenleri | Çalışır | Phase 1 | E kısmi; L yok | Kartlar | P2 |
| `/profile` | Profil / member | View + Volt profile formları | app | Çalışır | Breeze varsayılan, İngilizce kalıntılar | E/H form düzeyi | Profil/parola/silme formu | P1 |
| `/ne-pisirebilirim` | Ingredient matching / verified member | `IngredientExplorerController@index`, `ingredients/explore` | site | Çalışır | Phase 1; üç kolon form | E var; L yok | Çoklu filtre formu/sonuçlar | P1 |
| `/dolabim` | Pantry / verified member | `PantryController`, `pantry/index` | site | Çalışır | Phase 1 | E var; L yok | Ekle/düzenle/sil formu | P1 |
| `/favoriler` | Favorites / verified member | `FavoriteController@index`, `favorites/index` | site | Çalışır | Phase 1 | E var; L yok | Liste/remove | P1 |
| `/pisir/{session}` | Cooking Mode / verified member | `CookingSessionController`, `cooking-sessions/show` | site | Çalışır | Phase 1 | Session 404/H var; L yok | Step/timer/pause/resume | P1 |
| POST cooking/favorite/pantry routes | Member aksiyonları | İlgili controllerlar | n/a | Çalışır | UI sayfası değil | Validation/authorization | Form/Alpine | P1 regresyon |

## Admin ekranları

| Route ailesi | Sayfa / rol | Controller + view | İşlev | Görsel / durumlar | Form / tablo / grafik | Öncelik |
| --- | --- | --- | --- | --- | --- | --- |
| `/yonetim` | Genel Bakış / admin | `DashboardController`, `admin/dashboard` | Çalışır | Admin taslak; dark/mobile temel | Kartlar, gerçek metrik özeti; grafik yok | P1 |
| `/yonetim/tarifler` | Tarif listesi/create/edit / admin | `Admin\\RecipeController`, `admin/recipes/*` | Çalışır | Admin taslak | Arama/filtre/table/form; wizard yok | P1 |
| `/yonetim/tarifler/{recipe}/adimlar` | Tarif adımları CRUD/sıralama / admin | `RecipeStepController`, `admin/recipe-steps/*` | Çalışır | Admin taslak | Form/table; timeline preview yok | P1 |
| `/yonetim/malzemeler` | Ingredient liste/detay / admin | `IngredientController`, `admin/ingredients/*` | Çalışır | Admin taslak | Alias/substitution subformları; 360° detay yok | P1 |
| `/yonetim/beslenme` | Nutrient/fact/allergen/diet/snapshot/history / admin | `NutritionController`, `admin/nutrition/*` | Çalışır | Admin taslak | Çoklu form/table; grafik yok | P1 |
| `/yonetim/medya` | Media liste/yükle/detay / admin | `MediaController`, `admin/media/*` | Çalışır | Admin taslak | Upload/table/detail; gerçek recipe media eksik | P1 |
| `/yonetim/veri-kalitesi` | Data Quality / admin | `DataQualityController`, `admin/data-quality/*` | Çalışır | Admin taslak; empty/error var | Filtre/table/detail/resolve/reopen; grafik sınırlı | P2 |
| `/yonetim/basarisiz-isler` | Failed Jobs / admin | `FailedJobController`, `admin/failed-jobs/*` | Çalışır | Admin taslak | Filter/table/detail/retry/forget | P2 |
| `/yonetim/sistem-olaylari` | System Events / admin | `SystemEventController`, `admin/system-events/*` | Çalışır | Admin taslak | Filter/detail/resolve/reopen | P2 |
| `/yonetim/yedekler` | Backups / admin | `BackupController`, `admin/backups/index` | Çalışır | Admin taslak | Create/verify/download/cleanup | P2 |
| `/yonetim/restore-gecmisi` | Restore History / admin | `RestoreHistoryController`, `admin/restore-history/*` | Çalışır | Admin taslak | Liste/detail | P2 |
| Sistem status refresh POST'ları | Queue/cache/scheduler / admin | `DashboardController` | Çalışır | UI paneli taslak | Refresh aksiyonları; trend grafik yok | P2 |

Admin bütün route'ları `auth`, doğrulanmış e-posta ve `access-admin` policy'si ile korunur. Member için 403 sözleşmesi mevcut testlerle korunmalıdır.

## API v1 ve sistem endpointleri

16 endpoint: recipes (liste/detay/nutrition/allergen), recipe-match, ingredients (liste/nutrition/substitution), cuisines, categories, nutrients, allergens, dietary-tags, health/status/version. Hepsi public ID, request ID, rate limit ve OpenAPI sözleşmesine tabidir. Bunlar UI redesign kapsamı dışında tutulur; yalnız ilgili ekranların gerçek verisini besler.

## En kritik UI bulguları

1. Global V2 sadece home/header/footer ve tarif listesinin kontrastını kapsar; recipe detail, matching, pantry, favorites, cooking, profile/auth ve bütün admin ekranları görsel olarak tutarsızdır.
2. Admin sidebar hâlâ emoji/metin sembolleri ve legacy renk utility'leri kullanır; icon, form ve table sistemi merkezi değildir.
3. Loading skeleton, route seviyesinde loading/error state ve grafik empty state sözleşmesi çoğu ekran için yoktur.
4. Aynı hero görseli recipe card'larda tekrar edilir; recipe-level medya manifesti yoktur.
5. Guest/profile ekranlarında Breeze varsayılan düzeni ve İngilizce metinler kalmıştır.
