# Master Product Audit — Prioritized Backlog

> **Master Fix Phase A (2026-07-28):** AUD-001, AUD-003, AUD-004 and AUD-005 now have a transparent, testable baseline: semantic admin dark tokens, weighted Data Quality scoring, media/conversion/nutrition issue detection and local-only browser fixtures. Replacing placeholders with licensed approved media remains an editorial operation, not a completed engineering claim.

> **Master Fix Phase B (2026-07-28):** 10-recipe AI image pilot controls are implemented but actual generation is blocked. The selected catalogue records must first pass title/ingredient fidelity, then a cost and commercial-use approval is required. No external image was copied and no billable image call was made.

## P1 — Önce çözülmeli

| ID | Alan | Bulgu / çözüm hedefi | Efor | Bağımlılık |
|---|---|---|---:|---|
| AUD-001 | Admin shell | Dark token/opacity kontrastını WCAG AA ile düzelt; kart, chart, muted text ve sidebar’ı görsel regression ile doğrula. | M | V3 admin token audit |
| AUD-002 | Media/content | 50 placeholder medya kaydını lisans/provenance’lı gerçek veya onaylı üretim varlıklarıyla kontrollü olarak değiştir. | L | Media sourcing ve editorial onay |
| AUD-003 | Data Quality | Skor sözleşmesini “teknik bütünlük” ve “editoryal güven” olarak ayır; UI’da kapsamı açıkça göster. | M | AUD-004 |
| AUD-004 | Data Quality | Placeholder, düşük güven conversion, AI estimated nutrition, stale/absent snapshot için merkezi issue türleri ekle. | M | Skor ağırlıkları |

## P2 — Public/member/admin güveni ve ölçek

| ID | Alan | Çözüm hedefi | Efor |
|---|---|---|---:|
| AUD-005 | QA fixtures | Doğrulanmış ayrı local member fixture ve browser smoke akışı; kişisel veri olmadan. | S |
| AUD-006 | Recipe UX | Hero/card image aspect ratio, alt text, fallback ve approved-media status surface. | M |
| AUD-007 | SEO/content | Sitemap, canonical, Recipe/Organization JSON-LD; ardından About/FAQ/Privacy/Terms ve Blog roadmap. | L |
| AUD-008 | Motion | Reveal animasyonunu progressive-enhancement yap; initial paint’te metin görünür kalmalı. | S |
| AUD-009 | Accessibility | Grafiklerin yalnız bir metinsel özeti; canvas/img ilişkisini aria ile netleştir. | S |
| AUD-010 | Operations | Worker/scheduler never-run durumlarını “healthy”den ayır; dashboard CTA ve operatör yönergesi ekle. | M |
| AUD-011 | Nutrition ops | Snapshot freshness/invalidated queue görünürlüğü; eksik snapshot bakım görevi. | M |
| AUD-012 | Measurement | Düşük güven dönüşümlerini filtrele/inceleme kuyruğuna al; public metinde kesinlikten kaçın. | M |
| AUD-013 | Performance | RecipeSearch ve matching için DB-side filtre/batch hydration/profile baseline. | L |

## P3/P4 — Polish ve sürdürülebilirlik

- Footer bilgi linklerini gerçek bilgi sayfalarına yönlendirme (P3).
- Skip-link, form error summary, modal focus/keyboard ve 200% zoom test matrisi (P3).
- `X-Powered-By` prod hardening doğrulaması (P3).
- Yönetim formu dirty-state, sticky save, bulk confirmation ve table sticky-header standardı (P3).
- Loading/error/empty visual regression screenshot seti (P3).
- Motion easing/hız token standardı ve hover polish (P4).

## Öncelik formülü

`Impact × Frequency × Confidence / Effort`, 1–100 normalize edilir. P1 işlemler release öncesi, P2 işlemler içerik/SEO genişlemesinden önce planlanmalıdır.

## İlk 100 yürütme işi

Bu liste yeni özellik taahhüdü değil; denetim bulgularını uygulanabilir, bağımlı iş paketlerine bölmek içindir.

1. Admin koyu tema ana text tokenı; 2. muted text tokenı; 3. metric card değeri; 4. chart label; 5. sidebar link; 6. topbar breadcrumb; 7. dark visual regression; 8. 4.5:1 contrast raporu; 9. light/dark token sınırı; 10. dashboard period kontrolü.

11. Placeholder-media issue türü; 12. placeholder sayacı; 13. approved-media policy; 14. recipe media review kuyruğu; 15. source/license zorunluluğu; 16. alt-text edit denetimi; 17. focal-point politika; 18. crop QA; 19. mobile image QA; 20. approved image dashboard kartı.

21. Low-confidence conversion issue; 22. conversion source freshness issue; 23. AI-estimated nutrition issue; 24. snapshot freshness issue; 25. quality-score açıklama metni; 26. teknik/editoryal skor ayrımı; 27. false-green test fixture; 28. Data Quality penalty kalibrasyonu; 29. provenance coverage metriği; 30. media coverage metriği.

31. Local member fixture; 32. member browser login smoke; 33. member admin-403 smoke; 34. member favorite ownership smoke; 35. pantry ownership smoke; 36. cooking ownership smoke; 37. logout smoke; 38. expired pantry fixture; 39. empty favorites fixture; 40. matching explanation fixture.

41. Hero visible-first motion; 42. motion fallback without JS; 43. reveal duration budget; 44. reduced-motion visual test; 45. drawer escape test; 46. drawer focus restore; 47. modal focus trap test; 48. toast live-region test; 49. button press consistency; 50. chart animation fallback.

51. Skip link; 52. chart summary deduplication; 53. heading-order scan; 54. form error summary; 55. aria-describedby validation; 56. table scope/sort state; 57. icon accessible-name scan; 58. 200% zoom matrix; 59. keyboard-only recipe detail; 60. touch-target audit.

61. 320px public QA; 62. 390px public QA; 63. 430px public QA; 64. 768px public QA; 65. 820px public QA; 66. 1024px admin QA; 67. 1280px admin QA; 68. 1600px dashboard QA; 69. 1920px chart QA; 70. long-Turkish-text regression.

71. Recipe search profiler; 72. matching profiler; 73. nutrition snapshot hit/miss profile; 74. database-side nutrition prefilter; 75. dietary prefilter strategy; 76. allergen prefilter strategy; 77. batch hydration measurement; 78. large-catalog pagination benchmark; 79. dashboard chart query profile; 80. image LCP/CLS baseline.

81. Worker unavailable semantics; 82. scheduler never-run semantics; 83. stale heartbeat warning; 84. failed-job escalation copy; 85. backup freshness card; 86. operational action audit; 87. system status empty state; 88. safe retry confirmation; 89. log retention metric; 90. operator runbook links.

91. Sitemap; 92. canonical URLs; 93. recipe JSON-LD; 94. organization JSON-LD; 95. OG metadata; 96. privacy/terms pages; 97. about/how-it-works pages; 98. FAQ IA; 99. blog information architecture; 100. broken-link and metadata CI check.
