# Rollback plan

Every deployment keeps the previous application release, an immutable database backup, and the migration version that was deployed. A rollback is a release operation, not a source-control reset on the server.

1. Put affected write paths into maintenance mode if data integrity is at risk.
2. Stop/restart queue workers against the selected release; do not replay unknown failed jobs.
3. Switch the web root to the prior verified release and clear/rebuild Laravel caches.
4. Roll database changes back only when the migration is explicitly reversible. Otherwise restore a verified backup into a safe target after approval.
5. Recheck `/health`, API health/status/version, queue heartbeat, scheduler heartbeat, and a guest smoke route.
6. Record the incident without secrets, request identifiers, or raw personal data.

## Release klasörü ile geri dönüş

1. Önce en güncel verify edilmiş backup kimliğini ve veri migration riskini kaydedin.
2. Gerekliyse `php artisan down --render=errors::503` ile yazma trafiğini kontrollü durdurun.
3. `current` symlink'ini önceki CI-onaylı release'e değiştirin; `composer` veya kaynak üzerinde elle değişiklik yapmayın.
4. `php artisan optimize:clear`, cache rebuild ve `php artisan queue:restart` sonrası health/smoke kontrollerini çalıştırın.
5. Geri alınamaz migration varsa schema down çalıştırmak yerine onaylı backup'ı güvenli restore hedefinde doğrulayın; production restore yalnız operasyon onayıyla yapılır.
6. Health, API, queue, scheduler, mail ve guest/member smoke başarılıysa maintenance mode kapatılır.
