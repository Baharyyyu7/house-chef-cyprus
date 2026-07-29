# Image Generation Error Model

Durumlar: `request_created`, `provider_completed_without_image`, `provider_blocked`, `provider_failed`, `parser_failed`, `image_received`, `media_validation_failed`, `ready_for_review`, `approved`, `rejected`.

`generated` veya `completed` kullanılabilir görüntü anlamına gelmez. `ready_for_review`, private depolama, MIME/decode/limit doğrulaması sonrasında ve yalnız insan onayından önce kullanılır. Billing API yanıtında yoksa `unknown` kalır; ücretsiz olduğu varsayılmaz.
# B2 fallback error mapping

`IMAGE_PROVIDER_INSUFFICIENT_CREDITS`, `IMAGE_PROVIDER_RATE_LIMITED`, `IMAGE_PROVIDER_INVALID_POLLING_URL`, `IMAGE_PROVIDER_INVALID_RESULT_URL`, `IMAGE_PROVIDER_POLL_TIMEOUT` ve `IMAGE_PROVIDER_MEDIA_VALIDATION_FAILED` güvenli hata kodlarıdır. Hiçbiri ham provider response, header, token veya dosya yolu içermez.
