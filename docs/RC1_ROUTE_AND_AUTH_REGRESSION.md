# RC1 route ve yetki regresyonu

Route envanteri `release-candidate:rc1-audit --json` ile gerçek `Route` koleksiyonundan sayılır. Public sayfalar açık; üye sayfaları `auth`/gerektiğinde `verified`; yönetim sayfaları `auth`, `verified` ve ilgili Gate ile korunur. API v1 throttle altındadır; mutasyonlar POST/PUT/PATCH/DELETE ve CSRF sözleşmesiyle tanımlıdır.

Route adı, middleware veya kullanıcı sınırı değiştiğinde bu denetim ve Feature testleri tekrar çalıştırılmalıdır. Public URL’lerde route model binding için public slug/public ID kullanılır; internal integer ID gösterilmez.
