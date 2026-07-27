# Performance Baseline

Foundation B yerel SQLite baseline'ı `php artisan performance:profile` ile
ölçülür. Ölçüm sonucu cache durumu ve makine kaynaklarına bağlıdır; amaç mutlak
SLA değil, sonraki değişikliklerde sorgu regresyonunu yakalamaktır.

Seed veri setinde son temiz ölçüm:

| Akış | Sorgu | Süre |
| --- | ---: | ---: |
| Recipe listing | 3 | yaklaşık 5 ms |
| Recipe search | 46 | yaklaşık 13 ms |

`--json` seçeneği aynı metrikleri CI veya otomasyon tarafından okunabilir biçimde
verir. Cache ısındığında arama sorgu sayısı daha düşük olabilir.
