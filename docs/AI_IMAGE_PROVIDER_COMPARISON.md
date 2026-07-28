# AI Görsel Sağlayıcı Karşılaştırması

28 Temmuz 2026 erişim kontrolü:

| Sağlayıcı | Yapılandırma | Image capability | Canlı üretim |
|---|---|---|---|
| Gemini | Anahtar yapılandırılmış; model listesi 200 döndü ve image modellerini ilan etti | 10 kontrollü istek hiçbir kullanılabilir image payload'ı döndürmedi; durum güvenli `provider_failed` olarak kaydedildi | 0 görsel; retry kapalı |
| OpenAI | Yapılandırılmamış | Disabled | Yok |
| Stability | Yapılandırılmamış | Disabled | Yok |
| FLUX/BFL | Yapılandırılmamış | Disabled | Yok |
| ComfyUI | Endpoint yapılandırılmamış | Disabled | Yok |
| Diffusers | Endpoint yapılandırılmamış | Disabled | Yok |

Genel Gemini erişimi tek başına üretilmiş görsel yetkisi veya ticari kullanım kanıtı değildir. Bu nedenle iki sağlayıcı karşılaştırması ve gerçek kalite/maliyet ölçümü yapılmamıştır; sahte karşılaştırma yasaktır.

İki sağlayıcıyla gerçek kalite/maliyet karşılaştırması yapılamadı; yalnız Gemini yapılandırılmıştır. Sahte sağlayıcı karşılaştırması yapılmaz. Yeni bir sağlayıcı yapılandırılır ve ayrıca bütçe onayı verilirse aynı prompt şablonu ile ölçülecek başlıklar: tarif/malzeme doğruluğu, kültürel sunum, fotogerçekçilik, doku, renk, watermark, nesne bozulması, çözünürlük, süre, retry, maliyet ve ticari kullanım koşulu.
