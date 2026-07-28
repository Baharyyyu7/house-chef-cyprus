# Image Generation Error Model

Durumlar: `request_created`, `provider_completed_without_image`, `provider_blocked`, `provider_failed`, `parser_failed`, `image_received`, `media_validation_failed`, `ready_for_review`, `approved`, `rejected`.

`generated` veya `completed` kullanılabilir görüntü anlamına gelmez. `ready_for_review`, private depolama, MIME/decode/limit doğrulaması sonrasında ve yalnız insan onayından önce kullanılır. Billing API yanıtında yoksa `unknown` kalır; ücretsiz olduğu varsayılmaz.
