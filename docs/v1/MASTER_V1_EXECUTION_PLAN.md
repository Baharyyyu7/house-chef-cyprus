# House Chef Cyprus V1.0 yürütme planı

Bu plan, V1.0 kapsamını tek seferde tamamlanmış saymak yerine kanıtlanabilir ve geri alınabilir çalışma paketlerine böler. Otomasyon hiçbir içeriği kendi kendine onaylamaz; production deploy, gerçek kullanıcı verisi, lisanslı medya ve editoryal kararlar ayrı kapılardır.

## Başlangıç durumu

- Başlangıç commit'i: `c63fd3c80469a3951bf87d3b222232522057ef3a`
- Branch: `visual-redesign-v2`
- Route envanteri: 291 (184 admin, 31 API)
- Katalog: 500 tarif (50 published+verified, 450 draft), 200 blog (20 public, 180 draft)
- İçerik kalite denetimi: 630 bloklu, 0 otomatik yayın adayı
- Tarif medyası: 0 onaylı gerçek medya, 50 dürüst branded fallback
- SEO: 80/100, 5 ince içerik uyarısı, 0 kritik
- RC: 0 blocker, 0 critical; gerçek authenticated browser, erişilebilirlik ve production gözlemi gerekli

## Bağımlılık sırası

1. Baseline ve regresyon kanıtlarını sürekli güncel tut.
2. Published tarif/blog önerilerini private editör paketleriyle tamamla.
3. Lisans/provenance doğrulanmış medyayı insan onayına sun.
4. Public, member ve admin tarayıcı QA matrisini gerçek tarayıcıda tamamla.
5. Draft kimlik/duplicate bloklarını 25'lik kontrollü gruplarda insan kararına sun.
6. SEO, erişilebilirlik, performans ve güvenlik bulgularını ölçülebilir kapılardan geçir.
7. Backup/rollback ve artifact kanıtı olan onaylı release'i production'a taşı.

## Bitmiş sayılma kuralı

Bir iş yalnız test, statik kalite, gerçek tarayıcı kanıtı (gerekiyorsa), insan onayı (gerekiyorsa), commit ve production uygunluğu kaydedildiğinde tamamlanır. `PRODUCTION_READY`, `DEPLOYED` ve `VERIFIED` birbirinin yerine kullanılamaz.

## Operasyon döngüsü

`Audit → Repair → Validate → Test → Commit → Push → Review/approval → Artifact → Deploy → Live verification`

İnsan veya dış servis blokajı bulunan kayıt `BLOCKED` kalır; bağımsız teknik işler devam eder. Ayrıntılı durum [TASK_LEDGER.md](TASK_LEDGER.md), engeller [BLOCKERS.md](BLOCKERS.md), kabul kapıları [FINAL_ACCEPTANCE.md](FINAL_ACCEPTANCE.md) içindedir.
