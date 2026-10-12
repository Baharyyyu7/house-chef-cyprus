# 12 Ekim 2026 üçüncü tur: sayıya uyan malzeme adı, saatli süre, pt hazırlık notu

Canlı: 3297b03 (önceki yayın 070cfd0 aynı gün).

| Commit | Ne | Canlı ölçüm |
|---|---|---|
| 8e786c2 | DE/PT birimsiz satırda ad sayıya uyar (lang/{de,pt}/sayilabilir.php); DE sıfat zinciri ve zarflı ad küçük harf | 161 DE + 126 PT satır, uyumsuzluk 0 |
| 070cfd0 | SureMetni::dakika: 60 dk ve üstü saatle; zamanlayıcı s:dd:ss | semifreddo "6 sa 31 dk" / "6 h 31 min" / "6 Std. 31 Min." |
| 3297b03 | PT hazırlık notu adın cinsine/sayısına uyar (":o"), Brezilya sözcükleri; og_locale pt_BR | 61 notlu satır, mastar 0 |

Veri (IntegrityReport): 30698 katalogda 32 malzeme 6 dilde (yalnız taslaklar kullanıyordu); 30699 üç limon suyu satırı -> Limon + "suyu sıkılmış".

Ölçülüp kapatılan: olası kopya tarifler (ayrı yemekler, gösterim yok), "Adım N" başlıkları (açık sayfada 0), açık sayfalarda Türkçe harfli adlar yalnız özel adlar.

Tuzak: önbellek kırmak için eklenen `?v=` sorgu parametresi sayfayı noindex yapar; robots ölçümünü temiz adresle yap.
