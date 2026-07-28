# AI Görsel Pilot Maliyet Raporu

- Pilot tarif: 10
- Maksimum varyasyon: tarif başına 2, toplam 20
- Günlük bütçe: varsayılan `0` cent (hard stop)
- Birim maliyet: yapılandırılmadı
- Tahmini toplam: **belirlenmedi**

Fiyatı bilinmeyen çağrı için `0` maliyet gösterilmez. Üretimden önce seçilen modelin güncel fiyatı, birim maliyet, 20 varyasyon üst limiti ve günlük bütçe `.env` ile yazılacak; ardından kullanıcı açıkça onay verecektir. 403/402/429 yanıtları güvenli hata olarak kaydedilir; tekrar deneme bütçe ve varyasyon limitini aşamaz.
