# House Chef Cyprus — Master Product Audit

**Tarih:** 28 Temmuz 2026  
**Kapsam:** `visual-redesign-v2` üzerindeki public, üye, yönetim, veri, güvenlik ve operasyon yüzeyleri.  
**Yöntem:** Salt-okunur kod/route incelemesi, yerel HTTP doğrulaması, gerçek in-app browser ile guest/public ve admin akışlarının temsilî kontrolü, mevcut otomatik test paketi ve statik analiz kanıtları. Uygulama kodu veya katalog verisi değiştirilmedi.

## Başlangıç kanıtı

- Çalışma dizini: doğrulandı.
- Remote: `Baharyyyu7/house-chef-cyprus`.
- Branch: `visual-redesign-v2`; başlangıç commit’i `aa5d947`.
- Route envanteri: 153 HTTP route.
- Yerel `/`, `/login`, `/health` ve `/api/v1/health`: HTTP 200; bilinmeyen sayfa: HTTP 404.
- Otomatik kalite kanıtı: 149 testin 148’i başarılı, 1 beklenen güvenli skip; Pint ve PHPStan başarılı; Composer/npm üretim audit’lerinde bilinen açık yok.

## Skor kartı

| Alan | Puan | Gerekçe |
|---|---:|---|
| Public | 78 | V3 hiyerarşisi güçlü; görsel katalog hâlâ bütünüyle placeholder. |
| Member | 73 | Pantry, eşleştirme ve favori akışları işlevsel; gerçek member browser QA için seed kullanıcı yok. |
| Cooking | 80 | Oturum/zamanlayıcı sözleşmesi ve ownership testleri mevcut; uzun mobil uçtan uca QA sonraki turda gerekli. |
| Auth/Profile | 77 | Türkçe V3 yüzeyleri ve auth testleri var; gerçek e-posta doğrulama teslimi yerelde doğrulanmadı. |
| Admin dashboard | 62 | Veri hiyerarşisi iyi, fakat koyu tema kontrastı günlük kullanım için yetersiz. |
| Admin CRUD | 74 | Geniş kapsam, public-ID yönelimli bağlantılar ve responsive table shell var; uzun form/dirty-state polish eksik. |
| Data trust | 58 | Provenance ve doğrulama mevcut; 100/100 kalite skoru placeholder ve düşük güven sinyallerini temsil etmiyor. |
| Motion | 68 | Reveal, hover, drawer ve reduced-motion temel desteği var; ilk girişte hero metni kısa süre görünmüyor. |
| Responsive | 76 | 375/1024/1440 temsilî kontrollerinde yatay taşma yok; tüm hedef genişlik matrisi henüz otomasyona bağlı değil. |
| Accessibility | 70 | Semantik başlıklar, label’lar, focus ring ve reduced-motion var; skip-link, bazı grafik özet tekrarları ve kapsamlı klavye QA eksik. |
| SEO/Blog | 36 | `robots.txt` var; sitemap, canonical, yapılandırılmış veri, bilgi sayfaları, Blog/SSS yok. |
| **Overall Product** | **70** | Güçlü teknik çekirdek ve V3 yönü; admin dark kontrastı, gerçek medya, kalite-skoru doğruluğu ve SEO bilgi mimarisi önceliklidir. |

## En kritik 20 bulgu

| ID | Özet | Severity | Öncelik |
|---|---|---|---:|
| AUD-001 | Admin dashboard koyu temada metrik ve açıklama metinleri siyah yüzey üzerinde düşük kontrastlı. | P1 | 96 |
| AUD-002 | 50/50 tarif görseli `is_placeholder`; yalnız sekiz tekrar eden SVG yolu kullanılıyor. | P1 | 94 |
| AUD-003 | Data Quality 100/100, placeholder medya, 14 düşük güvenli dönüşüm ve 8 tahmini nutrition fact varken kullanıcıyı yanlış güvene yöneltebilir. | P1 | 92 |
| AUD-004 | DataQualityService placeholder medya/AI nutrition/düşük güvenli dönüşümü issue üretmiyor. | P1 | 89 |
| AUD-005 | Yerel seed’de yalnız admin var; gerçek member browser/authorization regresyonu tekrarlanabilir değil. | P2 | 78 |
| AUD-006 | Recipe detail ve kart kataloglarının tamamı temsili görsel; premium içerik algısı kırılıyor. | P2 | 77 |
| AUD-007 | SEO içerik merkezi yok: sitemap, canonical, JSON-LD, About/FAQ/Privacy/Terms/Blog route’ları yok. | P2 | 76 |
| AUD-008 | Home hero reveal ilk yüklemede opacity 0 ile başlıyor; yavaş/engelli JS koşulunda LCP içeriği geç görünür. | P2 | 72 |
| AUD-009 | Admin dashboard grafikleri erişilebilir metin özetini iki kez duyuruyor. | P2 | 69 |
| AUD-010 | Scheduler heartbeat “henüz veri yok”, queue worker “unavailable” iken genel sağlık durumu açık ve eyleme dönük değil. | P2 | 68 |
| AUD-011 | 48 tarifte nutrition snapshot yok; hesaplama fallback’i vardır fakat operasyonel freshness görünürlüğü düşük. | P2 | 67 |
| AUD-012 | 14 ölçü dönüşümü düşük güvenli; public metin temkinli olsa da kalite skoru etkilenmiyor. | P2 | 65 |
| AUD-013 | Ingredient matching ve search, sonuç filtresi için koleksiyonu uygulama belleğine alıyor; katalog büyümesinde performans riski. | P2 | 63 |
| AUD-014 | Header/footer’da platform bilgi bağlantıları tarif listesine yönleniyor; ayrı bilgi mimarisi yok. | P3 | 56 |
| AUD-015 | Skip navigation bağlantısı yok. | P3 | 54 |
| AUD-016 | `X-Powered-By` başlığı yerel yanıtta mevcut. | P3 | 52 |
| AUD-017 | Admin ingredient ve bazı yönetim formlarında internal integer ID form payload’ında kullanılıyor; görünür UI’de sızıntı yok, API dış sözleşmesinden ayrıştırılmalı. | P3 | 50 |
| AUD-018 | Aynı ana sayfa hero metninde reveal süresince kullanıcı blank hero görüyor; motion başarı için görünürlük fallback’i yok. | P3 | 48 |
| AUD-019 | Empty state’ler güçlü fakat loading/error durumlarının route bazında görsel bir test matrisi yok. | P3 | 45 |
| AUD-020 | 50 recipe catalog içerik alanları teknik olarak dolu olsa da 8 tekrarlanan placeholder görsel “demo” hissini sürdürüyor. | P3 | 44 |

Tam bulgu sözleşmesi ve fazlama: [MASTER_PRODUCT_AUDIT_BACKLOG.md](MASTER_PRODUCT_AUDIT_BACKLOG.md).

## Kanıt sınırlamaları

- Browser ile admin, public home/list/detail/pantry/data-quality yüzeyleri ve 375/1024/1440 temsilî viewport’ları incelendi.
- Seed veritabanında yalnız bir admin rolü bulunduğu için gerçek member browser oturumu oluşturulmadı; mevcut Feature testleri member 403, IDOR ve ownership davranışlarını kapsar.
- 419/500 gerçek hata ekranları kontrollü route oluşturulmadan tetiklenmedi; önceki güvenli QA backlog’u korunur.
- Bu rapor görsel ve veri denetimidir; penetrasyon testi değildir.
