# Hostinger Mail Capability Matrix

Son doğrulama: 2026-08-03.

| Capability | Durum | Kanıt / sınır |
|---|---|---|
| MX routing | AVAILABLE | `mx1.hostinger.com` ve `mx2.hostinger.com` |
| SPF | AVAILABLE | Hostinger SPF include kaydı mevcut |
| DKIM | AVAILABLE | Üç Hostinger DKIM selector CNAME kaydı mevcut |
| DMARC | AVAILABLE_WITH_LIMITS | Kayıt mevcut, policy `p=none`; enforcement otomatik yükseltilmedi |
| Autodiscover/autoconfig | AVAILABLE | Hostinger mail CNAME kayıtları mevcut |
| SMTP application contract | AVAILABLE | Laravel SMTP yapılandırması ve no-send health komutu mevcut |
| Queued registration alert | AVAILABLE | Idempotent, after-commit listener ve delivery status kaydı mevcut |
| Contact notification/reply | AVAILABLE | HTML + plain-text mailables mevcut |
| Verification/password reset | AVAILABLE | Laravel transactional mail akışları mevcut |
| Mailbox/alias/forward management API | NOT_VERIFIED | Hostinger Mail connector bu oturumda callable araç yayınlamadı |
| Bounce/complaint feed | NOT_AVAILABLE | Callable bounce API bulunmadı; başarı metriği uydurulmaz |
| Newsletter send | DISABLED | Consent, unsubscribe ve suppression list tamamlanmadan açılmaz |

SMTP parola, mailbox credential veya mesaj gövdesi admin sağlık ekranında gösterilmez.
