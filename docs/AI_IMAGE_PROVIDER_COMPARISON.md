# AI Görsel Sağlayıcı Karşılaştırması

28 Temmuz 2026 erişim kontrolü:

| Sağlayıcı | Yapılandırma | Image capability | Canlı üretim |
|---|---|---|---|
| Gemini | Anahtar yapılandırılmış; B1'in izinli tek recovery isteği `imagen-4.0-generate-001` yolunda HTTP 404 döndürdü | Mevcut anahtar/model/endpoint kombinasyonu kullanılamıyor; 20 tarihî kayıt telemetry içermediği için kök nedenleri ayrıca ispatlanamaz | 0 görsel; otomatik retry kapalı |
| OpenAI Image API | Yapılandırılmamış | B2 için değerlendirilmiş fallback; ayrı erişim, fiyat ve ticari kullanım doğrulaması gerekir | Yok |
| Stability | B2 adapteri hazır; anahtar yapılandırılmamış | `not_configured` | Yok |
| FLUX/BFL | B2 async polling adapteri hazır; anahtar yapılandırılmamış | `not_configured` | Yok |
| ComfyUI | Endpoint yapılandırılmamış | Disabled | Yok |
| Diffusers | Endpoint yapılandırılmamış | Disabled | Yok |

Genel Gemini erişimi tek başına üretilmiş görsel yetkisi veya ticari kullanım kanıtı değildir. Google'ın Gemini API Imagen belgeleri bu model ailesi için `:predict` sözleşmesini yayınlasa da mevcut proje yapılandırmasında model/endpoint erişilebilir değildir. Imagen 4 varyantlarının Ağustos 2026 kapanış takvimi de yeni üretim sağlayıcısı seçilirken ayrıca dikkate alınmalıdır. Bu nedenle iki sağlayıcıyla gerçek kalite/maliyet ölçümü yapılmamıştır; sahte karşılaştırma yasaktır.

İki sağlayıcıyla gerçek kalite/maliyet karşılaştırması yapılamadı; yalnız Gemini yapılandırılmıştır. Sahte sağlayıcı karşılaştırması yapılmaz. Yeni bir sağlayıcı yapılandırılır ve ayrıca bütçe onayı verilirse aynı prompt şablonu ile ölçülecek başlıklar: tarif/malzeme doğruluğu, kültürel sunum, fotogerçekçilik, doku, renk, watermark, nesne bozulması, çözünürlük, süre, retry, maliyet ve ticari kullanım koşulu.

B2 aday primary provider BFL FLUX.2 Pro, fallback provider Stability Stable Image Core'dur. Bu karar entegrasyon karmaşıklığı ve resmi API sözleşmesine dayanır; henüz gerçek görsel kalite sonucu değildir.
