# 12 Ekim dördüncü tur

## Kod
- a7f0037: EN tekil parantezli ad ("1 kapia pepper (sweet red)"); de/pt damla sakızı çiftleri.
- b413ccd: blog kategori adı 6 dilde (10 kategori); kart, karusel, kategori sayfası başlığı, iç bağlantı, llms.txt.
- Kıbrıs yazısı kutusu 7 dilde; TR hub'dan görünür anahtar kelime şeridi kalktı, SSS yanıtı okura yazıldı.
- pt dişle ölçülen sarımsakta not dişe uyar ("3 dentes de alho, ralados").

## Veri (yayın gerektirmedi)
- coni:malzeme 30701: #115 mastic tears / Mastixkörner / lágrimas de mástique.
- IntegrityReport 30702: İngilizce asıllı 15 rehberde düşmüş 667 boşluk geri kondu. Yalnız boşluk eklendiği mekanik olarak doğrulandı. Geri alma: `RAPOR=30702 php artisan tinker --execute='require "/home/u584361532/bosluk_geri_al.php";'`

## Ölçüm yöntemi
- Yapışma dedektörü: harf + [.:;,] + harf, harf + "(", ")" + harf; marka adları (PepperScale, FoodNavigator) yanlış alarm.
- Yabancı blog sayfalarında Türkçe harfli sözcük taraması `trtara.py` (scratchpad).
