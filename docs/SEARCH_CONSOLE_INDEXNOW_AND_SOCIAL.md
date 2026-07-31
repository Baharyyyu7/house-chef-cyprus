# Search Console, IndexNow and Social Controls

`SEARCH_CONSOLE_ENABLED=false`, `INDEXNOW_ENABLED=false`, `X_DISTRIBUTION_ENABLED=false` ve `X_AUTO_POST=false` güvenli varsayımlardır. Sağlayıcı health komutları secret göstermez; kapalı sağlayıcılar için ağ isteği oluşturmaz.

Etkinleştirme yalnız domain doğrulaması, OAuth/service account veya public key, veri saklama kararı, rate-limit/backoff, audit log ve insan onayıyla yapılır. Search performansında query metni yerine mümkünse hash saklanır.
