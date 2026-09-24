# Ana sayfa video ve CSS tek kaynak tamamlama

- Kesilen ana sayfa calismasinin `da7ad83` ile dalda oldugu dogrulandi.
- Ana sayfa arama, pantry ve utility stilleri tek kanonik CSS blogunda toplandi.
- Kullanici videosu 1280x720 sessiz MP4 ve WebM olarak optimize edildi.
- Video depoya eklenmedi; kalici shared public storage alanina yuklendi.
- Hero bileşeni videoyu public storage diskinden buluyor ve LCP gorselini poster/yedek olarak koruyor.
- Video yalniz genis ve yeterince hizli baglantida, Save-Data ve reduced-motion kapaliyken yukleniyor.
- WebM MIME ve MP4/WebM onbellek kurallari web root sozlesmesine eklendi.
- Medya SHA-256 degerleri yukleme sonrasinda uzak sunucuda dogrulandi.
