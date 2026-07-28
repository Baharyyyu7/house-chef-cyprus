# AI Görsel Pilot Maliyet Raporu

- Pilot tarif: 10
- Maksimum varyasyon: tarif başına 2, toplam 20
- Onaylanan günlük bütçe: **200 cent / 2 USD**
- Imagen 4 Standard referans birim fiyatı: **4 cent / görsel**
- 20 varyasyon tahmini üst sınırı: **80 cent / 0,80 USD**
- Bütçeden teorik kalan: **120 cent / 1,20 USD**

Fiyat Google'ın Imagen 4 Standard için yayımladığı fiyat sayfasından 28 Temmuz 2026'da doğrulandı. Bu model 17 Ağustos 2026'da kapanacak/deprecate edilecek; provider abstraction korunur ve kalıcı çözüm için alternatif model planlanmalıdır.

Gerçek pilotta 20 çıktı slotu istendi ancak sağlayıcı kullanılabilir çıktı döndürmedi. API yanıtında fatura satırı bulunmadığı için gerçek maliyet doğrulanamadı; **$0,80 yalnız üst sınır/rezerv tahminidir, gerçek maliyet olarak raporlanmaz.** Otomatik retry kapalıdır; yeni bir deneme hem 20 varyasyon hem 2 USD onayının yeniden açıkça verilmesini gerektirir.
