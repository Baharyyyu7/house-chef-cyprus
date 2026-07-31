# Production monitoring

## Sağlık sinyalleri

- `/health`, `/api/v1/health`, `/api/v1/status`, `/api/v1/version`
- `queue:health --json`, worker heartbeat, failed/stuck job sayısı
- scheduler heartbeat ve en son başarısız scheduled task
- backup create/verify sonucu ve restore drill tarihi
- database/Redis bağlantısı, disk alanı, HTTP 5xx oranı ve yavaş route özeti

## Uyarı ilkeleri

- `unhealthy` health sonucu, 5xx artışı, backup verify başarısızlığı, scheduler gecikmesi veya stuck job güvenli kanala alarm üretir.
- Alarm verisi request ID, güvenli hata kodu ve kısa özet içerebilir; token, cookie, raw IP, SQL, stack trace veya payload içermez.
- Gerçek gözlemleme DSN'si production secret store'a konur. Repo yalnız boş `SENTRY_DSN` benzeri environment alanları içerir.

## İnceleme sırası

1. Health ve queue/scheduler durumunu kontrol edin.
2. Son release ve migration'ı doğrulayın.
3. Backup verify sonucunu kontrol edin.
4. Gerekliyse yazma trafiğini maintenance mode'a alın; rollback planını uygulayın.
