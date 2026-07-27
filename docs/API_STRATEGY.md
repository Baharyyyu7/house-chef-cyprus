# API Stratejisi

`/api/v1` 16 aktif endpoint içerir. Başarılı tekil yanıtlar `data` ve `request_id`; koleksiyon yanıtları `data`, `meta`, `links` ve `request_id` taşır. Hatalar `error.code`, `error.message`, `error.details` ve `request_id` ile döner. Tüm istemci kimlikleri public ULID’dir.

OpenAPI 3.1 sözleşmesi [openapi.yaml](openapi.yaml) dosyasındadır ve `php artisan api:openapi-check --json` ile aktif route envanterine karşı doğrulanır. API v1 kararlı sözleşmedir; cursor pagination ve yeni sürüm yalnızca geriye dönük uyumluluk gerektirdiğinde değerlendirilir.
# Phase 2 measurement extension

Recipe detail responses retain legacy `ingredients` and add `ingredient_lines` with public ingredient identity, display quantity/measure, canonical quantity/unit, conversion confidence, preparation note, optional flag and group. Integer keys are not exposed.
