# Content Automation System

Otomasyon yalnız yerel ve doğrulanabilir platform verisini kullanır: yayın katalog boşlukları, anonim zero-result aramaları, içerik yaşı ve editoryal durumlar. `seo:content-opportunities`, `seo:content-refresh`, `seo:internal-links`, `seo:social-drafts` ve `seo:morning-brief` komutları varsayılan olarak dış istekte bulunmaz.

İçerik yaşam döngüsü: `idea → research → brief_ready → draft → fact_check → editorial_review → seo_review → media_review → approved → scheduled → published`. Yayın için fact-check, özgünlük, editoryal onay, kaynak notu, SEO metadata ve ilgili doğrulanmış tarif zorunludur.

Sosyal dağıtım yalnız private `social_distribution_drafts` taslağı üretir. X/Twitter paylaşımı otomatik değildir ve `X_AUTO_POST=false` varsayılanıdır.
