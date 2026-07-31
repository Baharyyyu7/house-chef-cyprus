# Unsplash tarif görseli iş akışı

1. Yönetici bir tarif için **Unsplash'ta Ara** seçer veya Pexels adayları uygunsuz kalır.
2. En çok beş adet küçük, güvenli metadata adayı kaydedilir; otomatik red yalnız sıralama/uyarı üretir.
3. Yönetici aday seçer ya da reddeder. Bu aşamada public medya ve tracking yoktur.
4. Final onay, alt metin ve editoryal not gerektirir. Onayda bir kez download tracking yapılır ve `external_hotlinked_media` oluşturulur.
5. Public tarif/blog sayfası yalnız `verified`, public ve izinli `images.unsplash.com` URL'sini gösterir; görünür fotoğrafçı/Unsplash attribution korunur.

Tracking başarısızsa otomatik retry döngüsü yoktur. Fallback korunur ve yönetici adayı daha sonra yeniden inceleyebilir.
