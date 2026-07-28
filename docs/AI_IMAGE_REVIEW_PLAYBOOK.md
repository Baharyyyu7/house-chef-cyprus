# AI Görsel İnceleme Playbook'u

## Ön koşul

Yalnızca private depoda bulunan ve `ready_for_review` durumuna ulaşmış adaylar incelemeye alınır. `provider_completed_without_image`, `provider_blocked`, `provider_failed`, `parser_failed` veya `media_validation_failed` kayıtları görsel adayı değildir; güvenli tanı kaydı olarak kalır ve public alana taşınamaz.

1. Tarif adı, ana malzemeler, pişmiş/çiğ durum ve porsiyon kaynak veriye uyuyor mu?
2. Kültürel sunum, tabak/kâse ve garnitür güvenilir mi?
3. Metin/logo/watermark, anatomik veya nesne bozulması var mı?
4. Mobil crop, focal point, alt text, çözünürlük ve thumbnail uygun mu?
5. Provider/model, prompt sürümü, lisans/ticari kullanım ve maliyet kaydı var mı?

Yanıtların tamamı olumlu değilse aday `rejected` kalır; private depodan public depoya çıkmaz. Onay sonrası dosya re-encode edilir, EXIF temizlenir, checksum/perceptual hash üretilir ve kalite taraması yeniden çalıştırılır.
