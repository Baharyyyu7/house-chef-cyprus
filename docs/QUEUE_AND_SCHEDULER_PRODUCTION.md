# Production queue ve scheduler

## Supervisor worker

`/etc/supervisor/conf.d/house-chef-worker.conf` için örnek:

```ini
[program:house-chef-worker]
process_name=%(program_name)s_%(process_num)02d
command=/usr/bin/php /var/www/house-chef/current/artisan queue:work redis --queue=default,media,seo --sleep=3 --tries=3 --max-time=3600
directory=/var/www/house-chef/current
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=housechef
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/house-chef/worker.log
stopwaitsecs=3600
```

Yayın sonrası `supervisorctl reread && supervisorctl update && php artisan queue:restart` kullanın. Başarısız job'lar admin ekranında yalnız güvenli özetle incelenir; raw payload, token veya stack trace gösterilmez.

## Cron scheduler

```cron
* * * * * cd /var/www/house-chef/current && /usr/bin/php artisan schedule:run --no-interaction >> /var/log/house-chef/scheduler.log 2>&1
```

`schedule:list`, scheduler heartbeat ve `queue:health --json` ilk yayın ve her incident sonrası kontrol edilir. Overlap korumalı görevler manuel paralel çalıştırılmaz.
# Sprint 1 operasyon notu

Supervisor şablonu default/reports/mail ile media/SEO/content/backups kuyruklarını ayırır: `deploy/supervisor/house-chef-worker.conf`. Cron şablonu her dakika `schedule:run` çağırır. Queue ve scheduler yalnız VPS üzerinde çalıştırılır; Mac/local worker production runtime değildir.
