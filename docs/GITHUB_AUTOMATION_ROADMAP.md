# GitHub Automation Roadmap

GitHub, House Chef Cyprus için yalnızca kaynak kod deposu değil; doğrulama, güvenlik, yayınlama ve proje kararlarının izlenebilir merkezi olacaktır. Bu belge planlama niteliğindedir: burada listelenen ileri aşama otomasyonları henüz etkinleştirilmemiştir.

## Aşama A — Her sprintte kalite kapısı

Mevcut CI korunur: PHPUnit, Pint, PHPStan, frontend production build, Composer audit, npm audit ve Dependabot. Her pull request için aşağıdaki kontroller hedeflenir:

- Test, format, statik analiz ve build zorunlu durum kontrolleri.
- Dependency ve secret kontrolleri; GitHub Secret Scanning uygun değilse `gitleaks` benzeri ücretsiz, açık kaynak bir alternatif.
- Action pinleme, en az yetki (`permissions`) ve üçüncü taraf workflow güvenlik denetimi.
- PR şablonu: kapsam, test kanıtı, migration, güvenlik/erişilebilirlik ve rollback etkisi.
- Semgrep Community veya PHP odaklı eşdeğer güvenlik taraması. Kural seti gerçek bulgularla kalibre edilmeden merge'i engellemez.

## Aşama B — SEO Content Hub

- Sitemap, canonical/metadata ve JSON-LD şema doğrulamaları.
- Dahili/kırık bağlantı denetimi.
- Lighthouse CI ile performans, SEO ve best-practices trend takibi.
- Axe tabanlı erişilebilirlik testi.
- Kritik tarif ekranları için visual-regression baseline; yalnızca stabil test verisiyle çalışır.

## Aşama C — Production

- Ayrı staging ve production deployment workflow'ları.
- GitHub Environment protection, onay kapıları ve environment secret'ları.
- Deploy öncesi yedek, deploy sonrası health check, ölçümlü rollback.
- Zamanlanmış güvenlik, dependency ve kalite denetimleri.

## Aşama D — Opsiyonel AI/GitHub özellikleri

- Copilot otomatik review, cloud coding agent, issue'dan taslak PR ve CI-fix agent; yalnızca organizasyon/plan erişimi mevcutsa değerlendirilir.
- Private repository veya ücretli plan kısıtları zorlanmaz. Erişim yoksa PR template, issue template, CI logları ve açık kaynak araçlarla aynı kontrol hedefi korunur.
