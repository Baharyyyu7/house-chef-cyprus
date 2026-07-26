# Privacy-Aware Geo Analytics Backlog

## Durum ve kapsam

Bu bir gelecek backlog'udur. Mevcut Technical Foundation çalışmalarına, ürün
akışlarına veya kullanıcı verisine müdahale etmez.

Hedef; ülke ve yaklaşık şehir düzeyinde performans analitiği, saat dilimi
tespiti, bölgesel içerik analizi ve sınırlı güvenlik sinyalleri sağlamaktır.
IP konumu her zaman yaklaşık bir sinyaldir; GPS, kesin adres veya erişim engeli
kararı değildir.

## Planlanan sözleşmeler

- `GeoLocationProviderInterface`
- `CloudflareGeoProvider` — yalnızca güvenilen proxy yapılandırmasında header okur.
- `MaxMindGeoLiteProvider` — yerel GeoLite verisiyle çalışır.
- `NullGeoProvider` — lokal geliştirme ve sağlayıcı yokluğu için varsayılan.
- `GeoLocationService` — sağlayıcı seçimi, normalizasyon, öncelik ve saklama
  politikasını uygular.

Lokal geliştirmede `NullGeoProvider` kullanılır. GeoLite veritabanı repoya
eklenmez; ayrı setup/update komutuyla kurulur ve `.gitignore` ile korunur.

## Saklanabilecek yaklaşık alanlar

- `country_code`, `country_name`
- `region_name`, `city_name`
- `timezone`, `accuracy_radius_km`
- `provider`, `first_seen_at`, `last_seen_at`

Şehir bilinmiyorsa yalnızca ülke düzeyi saklanır. Kullanıcının profilinde açıkça
seçtiği ülke/şehir, IP tabanlı tahminden önceliklidir.

## Kurallar

- VPN, mobil ağ ve proxy nedeniyle sonuçlar yaklaşık olarak etiketlenir.
- Analitik için raw IP yerine anonimleştirilmiş prefix veya hash tercih edilir.
- Raw IP yalnızca güvenlik/hukuki gereklilikte, sınırlı süreyle ve varsayılan
  admin ekranlarından gizli tutulur.
- Çerez ve gizlilik tercihleri analitik toplamayı sınırlar.
- Şehir verisi tek başına kritik iş kuralı veya erişim engeli olarak kullanılmaz.
- Sağlayıcı, doğruluk yarıçapı ve tahmin kaynağı her kayıtta izlenebilir olur.

## Gelecek admin görünümü

- Ülke ve yaklaşık şehir dağılımı
- Tarif, blog, SEO, reklam ve sponsor kampanyası performansı
- Şüpheli giriş sinyalleri
- Sağlayıcı sağlığı ve veri saklama ayarları

## Uygulama tetikleyicileri

Bu backlog yalnızca production hazırlığında, KVKK/GDPR aydınlatması ve saklama
politikası onaylandıktan; ayrıca trusted proxy, kullanıcı tercihleri ve güvenlik
tehdit modeli doğrulandıktan sonra uygulanır.
