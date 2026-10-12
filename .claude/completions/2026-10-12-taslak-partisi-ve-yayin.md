# 12 Ekim 2026 yedinci tur: ikinci taslak partisi, kapalı yayındaki tarifler, 10 tarif yayını

## Sonuç

| İş | Sonuç | Rapor |
|---|---|---|
| Katalog yarım çevirileri (#26, #46, #137, #156) | 24 ad düzeltildi | 30714 |
| Taslak yazımı: lahmacun-durum-8fq8v, krem-karamel | yazar + doğrulayıcı ajan, kapı temiz | 30715, 30716 |
| Taslak yazımı: revani-kibris (Şamali), hosmerim (Höşmerim) | yeni #268 Çiğ Badem İçi | 30717-30719 |
| coni:taslak --yayinda (1e9bb9b) | yalnız yayında + noindex tarif | canlı |
| Sütlü Revani, Etli Kabak Dolması baştan | yeni #269 Kabartma Tozu | 30720, 30722, 30723 |
| Etli Kabak Dolması indekse | 7 dil 200 + index | 30724 |
| Kapı Türkçe İ/I küçültmesi (5346fbe) | 1168 tarifte etki 6 | yayın bekliyor |
| Kullanıcı kararı: 11 taslak yayını | 10 yayında (günlük tavan 10), krem-karamel 13 Ekim | 30725 |
| Mutfak sınıfı (ekmek kadayıfı, höşmerim, krem karamel) | Kıbrıs -> Türk | 30726 |
| Nüsha 301 (5 taslak) | canlıda 301 | 30727 |
| 10 tarif x 6 dil çeviri | 60/60 canlı 200 + index | coni defteri |

## Kararlar

- Kıbrıs'ta revani = şamali iddiası kaynakla desteklenmedi (Havadis: hamura yağ girerse revani olur); başlık "Şamali (Kıbrıs İrmik Tatlısı)", slug revani-kibris korundu (slug değişimi otomatik 301 ile revani aramasını başka tatlıya taşırdı).
- Höşmerimin Kıbrıs kaynağı yok; Balıkesir tescili 187, country Türkiye, cuisine Türk.
- RecipePublicationService tamlık eşiği (95) kullanılmadı: yayındaki 127 tarifin yalnız 19'u geçiyor; kapı RecipeQualityGateService 11/11 temiz. Aynı alanlar raporlu betikle yazıldı.
- GuardsPublication günlük tavanı (10) aşılmadı.
- Sucuklu Yumurta (#1500) kapalı kaldı: #631 indekste, sabah açılıp geri alınmıştı (30682).
