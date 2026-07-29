# Stability AI Stable Image Core Provider

Adapter, Stability REST v2beta `stable-image/generate/core` endpointine multipart form gönderir. `accept: image/*` ile binary görüntü tercih edilir; ileride JSON/base64 dönüşü de parser tarafından desteklenir. Çıktı private depoya alınır, WebP olarak re-encode edilir ve insan review olmadan public edilmez.

Hata modelinde 403 moderation/engellenmiş üretim, 413 payload sınırı, 422 geçersiz istek ve 429 rate limit ayrı güvenli kodlarla tutulur. Anahtar yoksa adapter `not_configured` döner ve ağ çağrısı yapmaz.

Kaynak: [Stability REST v2beta API reference](https://platform.stability.ai/docs/api-reference).
