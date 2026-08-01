# Production Release States

Desteklenen durumlar: `development`, `local_qa`, `staging_ready`, `staging`, `production_ready`, `production`, `maintenance`.

Release-state kaydı son CI, smoke, backup, migration ve content-health tarama zamanlarını yalnız mevcut olduğunda tutar. Monitoring sağlayıcısı yoksa admin yüzeyi bağlantı beklediğini gösterir; veri uydurmaz.
