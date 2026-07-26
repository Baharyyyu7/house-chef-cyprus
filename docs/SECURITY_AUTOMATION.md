# Security Automation

Mevcut güvenlik kontrolleri Composer ve npm audit ile CI içinde yürür; Dependabot güncelleme önerileri depoda korunur.

Planlanan ekler: secret scanning veya ücretsiz gitleaks alternatifi, Semgrep Community, workflow permissions denetimi, haftalık schedule ile dependency/security taraması ve bulguların issue şablonuyla kayıt altına alınması.

GitHub Advanced Security, Copilot veya benzeri ücretli özellikler private repository planı tarafından desteklenmiyorsa etkinleştirilmez. Gizli değerler yalnızca GitHub Environments/Actions secrets üzerinden, deploy aşamasında verilir; `.env` hiçbir workflow loguna yazılmaz.
