# BFL FLUX.2 Provider

House Chef Cyprus B2 adapteri BFL FLUX.2 [pro] için `api.bfl.ai` global endpointini kullanır. İlk istek `x-key` ile JSON gönderir; yalnız sağlayıcının döndürdüğü `polling_url` takip edilir. Sonuç delivery URLsi backend tarafından indirilir ve private depoya aktarılır; kullanıcı tarayıcısına delivery URL verilmez.

Koruma: provider başına bir çağrı, toplam iki çağrı, otomatik retry yoktur. Polling üretim çağrısını tekrar etmez ve sınırlandırılmıştır. Delivery/polling URLleri yalnız HTTPS `*.bfl.ai` alanlarıyla sınırlandırılır. Signed delivery URLlerin kısa ömürlü olması nedeniyle sonuç hemen private storage'a alınır.

Kaynak: [BFL FLUX.2 text-to-image](https://docs.bfl.ai/flux_2/flux2_text_to_image), [BFL integration guide](https://docs.bfl.ai/api_integration/integration_guidelines).
