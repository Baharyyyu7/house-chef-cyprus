# Foundation V1 Durumu

Foundation V1, yerel demo içerikle çalışan ve dış import motoru olmadan kararlı bir tarif
uygulaması olarak dondurulmuştur.

## Tamamlanan temel

- Laravel altyapısı, SQLite, queue, scheduler ve cache yapılandırması
- Kayıt, giriş/çıkış, e-posta doğrulama akışı ve rol tabanlı yetkilendirme
- Tarif domain temeli; malzeme, mutfak ve kategori ilişkileri
- Favoriler ile son kullanma tarihi destekli malzeme dolabı
- Tarif arama, filtreleme, porsiyon ölçekleme ve yemek yapma modu
- Sağlayıcıdan bağımsız AI Şef demo provider
- Public ULID'ler ve API v1 temel endpoint'leri
- GitHub Actions ve Dependabot yapılandırması
- PHPUnit, Laravel Pint, PHPStan/Larastan ve frontend production build kontrolleri

## Bilinçli erteleme

Import Engine Foundation V1.5'e ertelenmiştir. Dış connector'lar, gerçek API bağlantıları,
import pipeline, retry/backoff, failed imports, raw payload saklama, veri kalitesi raporları ve
import yönetimi bu sürümün parçası değildir. Ayrıntılar `FOUNDATION_V1_5_BACKLOG.md` içindedir.

Bu karar, yarım/doğrulanmamış import akışının üretim davranışı veya kullanıcı yüzeyine
girmesini önler; mevcut demo uygulamasını dış servis ve lisans bağımlılıkları olmadan kararlı tutar.
