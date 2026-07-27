# Security Hardening

Foundation C güvenlik temeli; login throttle ve session regeneration, e-posta
doğrulaması, admin Gate/policy kontrolleri, API rate limit/validation ve
production'a güvenli HTTP başlıklarını kapsar.

`SecureHeaders` CSP, nosniff, referrer, permissions, frame, COOP/CORP
başlıklarını merkezî biçimde ekler. HSTS yalnızca production HTTPS isteğinde
etkindir. CORS yalnızca `api/*` için environment allowlist'inden gelir; wildcard
ve credentials varsayılan olarak kapalıdır.

`SafeUrlValidator`, private/loopback IP, localhost, geçersiz şema, kullanıcı
bilgisi ve standart dışı port içeren dış URL'leri engeller. Dış HTTP istemcisi
eklendiğinde redirect sonrası hedef de aynı doğrulamayla denetlenmelidir.

Local seed yöneticisi yalnızca local/testing ortamında oluşturulur. Production
admin hesabı deployment sırasında güvenli bir yönetim prosedürüyle atanmalıdır.
# Media operations

Media uploads use MIME plus magic-byte validation, strict format/extension allowlists, size/pixel limits, random storage names and decode/re-encode processing. Private media is served only through an authenticated authorization path. Maintenance commands are dry-run by default and record destructive cleanup actions in the audit log.
