# Güvenlik

`.env`, SQLite veritabanı, anahtarlar ve yedekler commit edilmez. Dış URL'ler SSRF doğrulamasından geçmeli, upload MIME/boyut kontrolüyle queue üzerinden işlenmelidir. CI, takip edilen dosyalarda temel secret ve yasak dosya taraması yapar; bu tarama profesyonel secret yönetiminin yerine geçmez.

Güvenlik açığını public issue yerine proje sahibiyle özel olarak paylaşın. Production için `APP_DEBUG=false`, HTTPS, `SESSION_SECURE_COOKIE=true`, dar CORS origin listesi ve local demo yöneticisinin olmaması zorunludur.

Foundation E kapanışı, production secret, domain/SSL ve gerçek staging hata sayfası QA işlerini otomatik olarak tamamlamaz.

Livewire/Alpine tabanlı mevcut arayüz, ifade değerlendirmesi için CSP'de `unsafe-eval` gerektirir. Bu istisna, giriş ve hesap akışlarının çalışması için uygulanmıştır; production CSP hardening aşamasında nonce tabanlı veya derleme tabanlı alternatifle yeniden değerlendirilmelidir.
