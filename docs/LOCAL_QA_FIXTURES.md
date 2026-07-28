# Yerel QA Fixture’ları

`php artisan qa:seed-fixtures` yalnız `local` ve `testing` ortamlarında deterministik QA hesapları oluşturur. Production ortamında komut güvenli biçimde başarısız olur.

- Yerel admin: `admin@housechef.local`
- Member A: favori, pantry ve devam eden pişirme oturumu içerir.
- Member B: ownership / IDOR kontrolleri için ayrı veriye sahiptir.

Yerel fixture parolası `ChangeMe123!` değeridir; yalnız geliştirme ortamındadır ve production secret değildir. `--reset` yalnız Member A/B fixture’larını siler; `--validate-only` veri yazmadan mevcut durumu raporlar. Tema tercihi kullanıcı kaydında değil, tarayıcının `localStorage.theme` anahtarında tutulur.
