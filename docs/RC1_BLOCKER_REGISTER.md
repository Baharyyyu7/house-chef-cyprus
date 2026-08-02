# RC1 blocker kaydı

Teknik denetim, blocker/critical seviyelerini `release-candidate:rc1-audit --json` ile üretir.

| Tür | Kaynak | Otomatik davranış |
| --- | --- | --- |
| BLOCKER | published fakat unverified tarif, yayın zamanlı draft blog, private medya sızıntısı | RC1 başarısız |
| CRITICAL | açık kritik SEO sorunu veya statik UX kritik sözleşme kaybı | RC1 başarısız |
| Dış bağımlılık | hosting/DNS/TLS/SMTP/monitoring/field CWV | Production launch insan onayı bekler |

2.340 fact-check konusu insan review backlog’udur; sistem bunu otomatik kapatmaz.
