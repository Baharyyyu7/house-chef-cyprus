# RC1 operasyon regresyonu

Environment/database/queue/scheduler/mail/backup/security/release-manifest komutları güvenli JSON sözleşmesi sağlar. Artifact, shared-hosting deploy, post-deploy smoke, rollback ve bakım scriptleri dry-run destekler.

Hosting, domain, TLS, SMTP, off-server backup ve monitoring yapılandırılmadıysa durum `not_configured`/`degraded` kalır; healthy taklit edilmez.
