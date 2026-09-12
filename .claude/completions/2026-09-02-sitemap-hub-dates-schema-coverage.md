# Sitemap hub tarihleri ve şema muhafızı kapsamı

## Yapılanlar

- Sitemap'teki veriyle beslenen altı hub için doğal `lastmod` kaynağı eklendi:
  ana sayfa, tarifler, blog, malzemeler, mutfaklar ve kategoriler.
- Doğal değişiklik tarihi bulunmayan politika/kurumsal sayfalarda `lastmod`
  bilerek boş bırakıldı; yapay tarih üretilmedi.
- Şema sözleşmesi muhafızı Eloquent sorgu koleksiyonlarında dolaşılan model
  değişkenlerini ve açık dönüş tipli Eloquent ilişki zincirlerini çözer hale
  getirildi.
- Muhafızdaki atlanan yazım sayısı 52'den 42'ye düştü; kanıtlanabilir taşma
  sayısı sıfır kaldı. Stripe SDK çağrıları gibi veritabanı olmayan property
  zincirleri tahmin edilmedi.
- Kategori sayfası, varsa anlamlı mevcut `description` alanını gösteriyor.
  Üretimdeki genel içe aktarma yer tutucusu içerik derinliği gibi sunulmuyor.
- Canlı `/blog` ItemList doğrulandı: görünen 12 kayıtla birebir 12 öğe ve her
  öğede yalnız `@type`, `position`, `url` var.

## Doğrulama

- Odaklı testler: 15/15 geçti.
- `db:schema-contract`: 535 çözülen yazım, 42 atlanan yazım, 0 bulgu.
- `git diff --check`: temiz.
- Tam test paketi: 995/995 test, 5.137 assertion geçti.

## Bilinçli olarak yapılmayanlar

- `config/locales.php` içindeki dil etkinlikleri değiştirilmedi.
- İçerik üretilmedi veya yayınlanmadı.
- NVIDIA/Hostinger paralel çalışma dosyalarına dokunulmadı.
- Commit, push veya deploy yapılmadı.
