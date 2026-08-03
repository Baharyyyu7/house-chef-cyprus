# V1.0 görev defteri

Son baseline: 2026-08-03. Durumlar yalnız gözlenen kanıta dayanır.

| ID | Alan | İş | Bağımlılık | Öncelik / risk | Durum | Dosya / migration / test | Commit | Production uygunluğu | Blokaj / sonraki aksiyon |
|---|---|---|---|---|---|---|---|---|---|
| V1-001 | Baseline | Katalog, SEO, medya, UX, RC ve operasyon denetimi | Yerel seed | P0 / düşük | COMPLETED_LOCAL | Artisan denetimleri | `c63fd3c` tabanı | Hayır; local kanıt | Bu defteri her batch sonrası güncelle |
| V1-002 | Published tarif | İlk 10 tarif için private current/proposed editör paketi | İnsan editör | P0 / yüksek | PUSHED | `PreparePublishedRecipeRepairBatch`, feature testleri | `78dcd37` | Hayır | Editör ve fact-check onayı |
| V1-003 | Published tarif | Kalan 40 tarifin sorun envanteri | V1-002 | P0 / yüksek | PUSHED | `QueuePublishedRecipeReviewBatch` | `8332d47` | Hayır | 16 kimlik uyumsuzluğu; 40 medya; içerik önerileri hazırlanmalı |
| V1-004 | Draft tarif | 450 draftı 18x25 kontrollü audit | İnsan kimlik kararı | P0 / yüksek | PUSHED | `--all`, duplicate grouping testleri | `c63fd3c` | Hayır | 450 `BLOCKED_IDENTITY_UNKNOWN`, 41 near-duplicate aile |
| V1-005 | Published blog | 20 public blogun içerik/fact-check önerileri | Kaynak ve editör | P0 / yüksek | BLOCKED | Mevcut factory/fact-check altyapısı | — | Hayır | Sağlık/gıda güvenliği iddiaları için kaynak+insan onayı |
| V1-006 | Medya | 50 tarife dish-accurate onaylı medya | Lisans/provider/insan | P0 / yüksek | BLOCKED | `recipe-images:audit` | — | Hayır | 0 onaylı gerçek medya; otomatik onay yasak |
| V1-007 | SEO | Beş ince tarif açıklamasını private öneriyle iyileştir | Editör | P1 / orta | QUEUED | `seo:audit --json` | — | Onay sonrası | Beş warning için özgün metin+onay |
| V1-008 | Public UX | Public/member/admin gerçek browser matrisi | Çalışan ortam+fixture | P0 / orta | TESTING | UX audit 82; Playwright public 375/1440 ve member login/dashboard smoke | — | Hayır | Kalan 320–1920, dark, keyboard, screen reader, 200% zoom ve admin akışları |
| V1-009 | Auth/account | Registration/auth/account regresyon kapısı | SMTP hariç local | P0 / yüksek | QUEUED | Mevcut auth ve member testleri | — | Test sonrası | Gerçek SMTP/verification production smoke ayrıca gerekli |
| V1-009A | User platform | Private tarif koleksiyonları | Verified member | P1 / orta | COMPLETED_LOCAL | Forward migration, policy, controller, UI, 4 feature testi | pending | Migration+QA sonrası uygun | Commit, push ve browser smoke |
| V1-010 | Community | Rating/comment/submission moderation sözleşmesi | Product+moderasyon onayı | P2 / yüksek | BLOCKED | Public launch kapalı tutulmalı | — | Hayır | Politika, abuse, spam, insan moderasyonu |
| V1-011 | Operations | Backup/queue/scheduler/health | Production config | P0 / yüksek | TESTING | Backup healthy; queue local worker unavailable | — | Hayır | Production cron/worker/SMTP/monitoring kanıtı |
| V1-012 | Release | Tam artifact, CI, deploy, rollback ve live smoke | V1 kabul kapıları | P0 / yüksek | BLOCKED | Release scriptleri mevcut | — | Hayır | İnsan deploy onayı ve tüm production bağımlılıkları |

## Güncel ölçümler

- `content:production-audit --json`: 500 tarif, 200 blog, 630 bloklu, 0 ready-for-human-review; auto-publish kapalı.
- `recipe-images:audit --json`: 50 public tarif, 0 verified real media, 50 fallback; alt/thumbnail/licence alanları mevcut.
- `seo:audit --json`: skor 80, 5 warning, 0 critical.
- `seo:sitemap-audit --json`: 15 sayfa, 50 tarif, 20 blog; draft sızıntısı raporlanmadı; onaysız 70 image girdisi dışlandı.
- `release-candidate:rc1-audit --json`: 0 blocker/critical, 5 medium; authenticated browser ve production bağımlılıkları manuel kapı.
