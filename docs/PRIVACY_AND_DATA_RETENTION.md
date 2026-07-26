# Gizlilik ve Veri Saklama İlkeleri

## Geo analytics ilkesi

Gelecekteki coğrafi analitik, kullanıcı konumunu yaklaşık ülke/şehir sinyali
olarak ele alır. IP tabanlı sonuçlar GPS, ev adresi veya kesin fiziksel konum
olarak sunulmaz.

## Veri minimizasyonu

- Analitik için anonimleştirilmiş IP prefix/hash kullanılır.
- Raw IP varsayılan olarak toplanmaz, admin arayüzünde gösterilmez.
- Raw IP gerektiğinde yalnızca güvenlik veya hukuki zorunluluk amacıyla,
  merkezi ayarla belirlenen en kısa süre saklanır.
- Şehir verisi yoksa veya düşük güvenliyse ülke düzeyine düşülür.

## Kullanıcı tercihleri ve öncelik

Çerez/gizlilik tercihleri izlenir. Kullanıcının açıkça seçtiği profil ülkesi veya
şehri, IP tahmininden önceliklidir. Konum verisi kişiselleştirme ve raporlama
için kullanılabilir; erişim engeli veya kritik kararlar için tek başına
kullanılamaz.

## Sağlayıcı güvenliği

Cloudflare konum header'ları yalnızca trusted proxy yapılandırması doğrulandığında
kabul edilir. Lokal ortamda konum çıkarımı yapılmaz (`NullGeoProvider`). GeoLite
veritabanı repository'ye commit edilmez.

## Uyum hazırlığı

Uygulama öncesinde KVKK/GDPR aydınlatma metni, saklama süreleri, silme/anonimleştirme
işleri, erişim kayıtları ve veri sahibi talepleri için süreç oluşturulur.
