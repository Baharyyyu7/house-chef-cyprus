# Gemini Görsel Başarısızlığı Tanısı

28 Temmuz 2026'daki önceki 20 pilot slot için eski adapter yalnız güvenli genel hata kaydettiği için HTTP gövdesi, durum kodu ve request ID geriye dönük olarak bilinmiyor. Bu kayıtlar `provider_failed`, `historical_response_telemetry_unavailable` ve `billing_status=unknown` olarak korunur; ham response saklanmaz.

Tek izinli `diagnostic_recovery` çağrısı Domates Çorbası için yapıldı. Sonuç: HTTP **404**, JSON response, 0 candidate/part/image, `IMAGE_PROVIDER_MODEL_UNAVAILABLE`, 375 ms, 880-byte request ve 248-byte response. Bu, mevcut Gemini API anahtarı/endpoint kombinasyonunda `imagen-4.0-generate-001` model yolunun kullanılabilir olmadığını gösterir. Otomatik retry yapılmadı.

Adapter, Google'ın Gemini API REST sözleşmesindeki `v1beta/models/{model}:predict`, `instances[].prompt`, `parameters.sampleCount` ve `predictions[].bytesBase64Encoded` yapısını uygular. Imagen 4 Standard Gemini API'de tanımlıdır ancak Google modeli 17 Ağustos 2026'da kapatacağını duyurmuştur. Kaynaklar: [Imagen modeli](https://ai.google.dev/gemini-api/docs/models/imagen), [Imagen REST rehberi](https://ai.google.dev/gemini-api/docs/imagen), [güncel image generation rehberi](https://ai.google.dev/gemini-api/docs/image-generation).
