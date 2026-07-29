# Master Fix Phases

## Faz A — completed engineering baseline

Semantic dark-mode tokens, explainable category scoring, real media/conversion/nutrition signals, local QA fixtures and image audit tooling were delivered on `visual-redesign-v2`. Editorial replacement of placeholder media continues in the controlled content backlog.

## Faz A — P1 güven, kontrast ve skor doğruluğu

- AUD-001..004.
- Hedef: Admin dark AA contrast, gerçek/approved media planı, Data Quality kapsamı ve skor açıklaması.
- Kapanış: dark visual regression, quality scan fixtures, admin browser QA.

## Faz B — AI görsel pilotu (B1 tanı/recovery tamamlandı, üretim bloklu)

10 tariflik AI image pilotu için provider envanteri, maliyet hard-stop, prompt fidelity gate, admin inceleme taslakları ve medya metadata sözleşmesi hazırlandı. B1'de ilk 20 başarısız kayıt korunarak güvenli telemetry backfill'i yapıldı; Domates Çorbası için izinli tek tanı çağrısı HTTP 404 ile model/endpoint erişilemezliğini doğruladı. Kullanılabilir medya oluşmadı ve otomatik retry uygulanmadı. Yeni üretim ancak ayrı model/sağlayıcı erişim doğrulaması ve yeni bütçe onayı ile B2 olarak açılabilir.

## Faz B2 — Fallback provider karşılaştırması

BFL FLUX.2 Pro async polling ve Stability Stable Image Core multipart adapterleri, yalnız Domates Çorbası için provider başına tek çağrı sınırıyla hazırlandı. Anahtar yoksa çağrı yapılmaz. İki private sonuç oluşmadan kalite puanı, primary seçimi veya public medya kararı verilmeyecektir.

## Faz B1 — Public/member güven deneyimi

- Media rollout, recipe trust labels, member QA fixture, nutrition snapshot freshness.
- Kapanış: 375/768/1440 public/member screenshots, no-placeholder policy ölçümü.

## Faz C — Admin günlük iş akışı

- Scheduler/worker operatör dilini, form dirty/save/confirmation standardını ve chart accessibility’yi tamamla.

## Faz D — Accessibility, responsive, motion

- Skip-link, chart summary dedupe, 200% zoom, keyboard drawer/modal, progressive motion.

## Faz E — Performance & scale

- Search/matching profiler, DB-side prefilter, snapshot warmup; baselines ile doğrulama.

## Faz F — SEO Content Hub

- Canonical/schema/sitemap/information pages, ardından Blog/FAQ. Data trust/media fazları kapanmadan başlanmamalı.

## Faz G — Full regression & production readiness

- Route smoke matrix, browser visual matrix, auth roles, backup/restore, security headers, release checklist.
