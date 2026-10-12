# 12 Ekim beşinci tur: gece çeviri içe aktarımı

## Bulgu
- Açık tarif pt/pl/fa 96, en/de/ru 123. 27 tarif x 3 dil kapıda "sablon".
- `content:import-translations --apply` her gece 02:20 UTC (ve 02:45 `seo:autonomous-growth` içinde) teslim klasörünü yeniden okuyup dolu çeviriyi eziyordu. 12 Ekim 02:20: 1098 alan (pt/pl/fa 27 tarif başlık/özet/açıklama, 131 adım başlığı, 131 talimat, 15 ipucu; en 14, ru 10 alan). 02:46: 3240 ve 3510 pt blog yazılarının pt-BR çevirisi eski Avrupa Portekizcesine döndü (5 alan).
- Eski kodla bu gece 30702 (15 EN rehber gövdesi) da geri dönecekti: teslim dosyaları yapışık metni taşıyor.

## Veri (yayın gerektirmedi)
- IntegrityReport 30704: 1098 satır 00:20 yedeğinden (01M4PQQQ9T6801ETT6Y58EJPNV). pt/pl/fa 96 -> 123.
- IntegrityReport 30705: 5 pt blog alanı yedekten. Şimdiki değerin 30634 "once" ile birebir olduğu doğrulandı.
- Geri alma (ikisi için): `RAPOR=<id> php artisan tinker --execute='require "/home/u584361532/bosluk_geri_al.php";'`

## Kod
- 9440ba0: dosya yalnız eksik alanı doldurur, dolu çeviri ezilmez; adımları kalıp olan dosya reddedilir. Bilinçli değiştirme yolu `coni:ceviri-toplu --zorla`.

## Ölçüm yöntemi
- Yedekten tablo çıkarma: `INSERT INTO \`content_translations\` VALUES` satırından `UNLOCK TABLES`'a kadar awk; satırlar ayrı, Python ayrıştırıcı scratchpad `onarim/dump_oku.py`.
- Gece penceresi: `content_translations.updated_at` 02:19-02:29 ve 02:45-02:50.
