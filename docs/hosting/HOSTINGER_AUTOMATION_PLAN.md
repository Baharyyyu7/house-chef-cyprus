# Hostinger Automation Plan

## Mevcut cron sözleşmesi

1. Her dakika: `cd /home/u584361532/domains/housechefcyprus.com/current && /usr/bin/php artisan schedule:run --no-interaction`
2. Her beş dakika: shared bounded queue-worker scripti.

Laravel scheduler; sağlık, backup, log retention, SEO audit, sitemap audit, analitik toplulaştırma ve editoryal hazırlık görevlerini overlap kilidiyle yönetir. İnsan onayı gerektiren içerik veya medya otomatik yayınlanmaz.

## Owner action sınırı

Hostinger cron ve DNS şu anda yalnız doğrulandı. Değişiklik ancak açık production yetkisi ve release doğrulamasıyla yapılır. Google ownership/OAuth işlemleri Hostinger otomasyonunun parçası değildir.
