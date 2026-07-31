# Post-deploy smoke testi

1. HTTPS canonical URL, `/health`, API health/status/version ve `robots.txt` 200 döner.
2. Sitemap index, recipe/blog/static/image alt sitemap'leri public ve canonical URL'ler döndürür.
3. Ana sayfa, tarif listesi, doğrulanmış tarif detayı, blog listesi ve blog detayı açılır.
4. Guest login/register, member favorite/pantry/cooking-session ownership, admin 403 guard ve private media erişim sınırları kontrol edilir.
5. Admin yalnız yetkili hesapla queue/system status, backup history ve media review ekranlarını açabilir.
6. E-posta doğrulama/reset yalnız güvenli test alıcısıyla yapılır.
7. `queue:health`, `schedule:list`, backup verify, cache warmup ve browser console hata özeti kaydedilir.
8. Onaylanmış gerçek görsel yoksa branded fallback'in görünür şekilde gerçek fotoğraf olmadığını doğrulayın; candidate hiçbir şekilde otomatik public edilmez.
