# Media Storage

`MediaStorageInterface` uygulama kodunu storage sağlayıcısından ayırır.
Mevcut `LocalMediaStorage` public disk üzerinde sadece JPEG, PNG ve WebP kabul
eder; MIME, boyut, decode/dimensions ve maksimum pixel kontrolü uygular. Dosya
adı rastgele üretilir; kullanıcı path'i kullanılmaz. SVG ve executable dosyalar
varsayılan olarak reddedilir.

Public disk tarif/thumbnail gibi güvenli çıktılar içindir. Raw upload, geçici
işleme, export ve backup dosyaları `local` private disk üzerinde tutulmalıdır;
doğrudan web root'tan servis edilmez. S3/R2 sağlayıcısı aynı interface'i
uygulayarak eklenebilir.

`media:scan-orphans` public `media/` yolundaki yetim dosyaları,
`media:cleanup-temporary` ise private `temporary/` yolundaki yaşlı dosyaları
raporlar. İki komut da varsayılan olarak dry-run'dır; silme işlemi production
operasyon prosedüründe ayrı onayla ele alınır.
