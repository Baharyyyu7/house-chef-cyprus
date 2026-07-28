# Image Provider Response Contract

Adapter ham response saklamaz. Güvenli snapshot: HTTP durum, Content-Type, body varlığı, candidate/part/image/text sayıları, finish/moderation sonucu, request/response byte sayısı, latency, parser sonucu ve güvenli hata kodudur.

Desteklenen image alanları: Imagen `predictions[].bytesBase64Encoded` + `mimeType`, Gemini candidate `content.parts[].inlineData` ve snake_case eşdeğeri. File URI varlığı teşhis edilir fakat ek ağ isteği yapılmaz. Yalnız PNG/JPEG/WebP, geçerli base64 ve merkezi boyut limiti kabul edilir.
