# API Stratejisi

`/api/v1` 16 aktif endpoint içerir. Başarılı tekil yanıtlar `data` ve `request_id`; koleksiyon yanıtları `data`, `meta`, `links` ve `request_id` taşır. Hatalar `error.code`, `error.message`, `error.details` ve `request_id` ile döner. Tüm istemci kimlikleri public ULID’dir.

OpenAPI 3.1 sözleşmesi [openapi.yaml](openapi.yaml) dosyasındadır ve `php artisan api:openapi-check --json` ile aktif route envanterine karşı doğrulanır. API v1 kararlı sözleşmedir; cursor pagination ve yeni sürüm yalnızca geriye dönük uyumluluk gerektirdiğinde değerlendirilir.
