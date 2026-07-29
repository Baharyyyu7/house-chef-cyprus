# Image Provider Response Contract

Adapter ham response saklamaz. Güvenli snapshot: HTTP durum, Content-Type, body varlığı, candidate/part/image/text sayıları, finish/moderation sonucu, request/response byte sayısı, latency, parser sonucu ve güvenli hata kodudur.

Desteklenen image alanları: Imagen `predictions[].bytesBase64Encoded` + `mimeType`, Gemini candidate `content.parts[].inlineData` ve snake_case eşdeğeri. File URI varlığı teşhis edilir fakat ek ağ isteği yapılmaz. Yalnız PNG/JPEG/WebP, geçerli base64 ve merkezi boyut limiti kabul edilir.
# Fallback provider notu

BFL async yanıtta yalnız provider'ın verdiği polling URL ve `result.sample` delivery URLsi kullanılır. Stability binary `image/*` veya JSON/base64 sonucunu merkezi parsera verir. Her iki durumda da ham body saklanmaz ve image olmayan 2xx yanıtlar başarı sayılmaz.
