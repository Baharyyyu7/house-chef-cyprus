# Media Provider Policy

| Provider | Kullanım sınırı | Public kapı | Not |
| --- | --- | --- | --- |
| Pexels | Private import öncesi editoryal aday seçimi | İnsan fidelity, lisans ve attribution incelemesi | HTTPS allowlist, redirect kapalı, MIME/magic/size denetimi |
| Unsplash | Resmî API aday metadata ve resmî hotlink politikası | İnsan onayı ve download tracking | UTM/creator attribution korunur |
| Gemini / Eden AI / BFL / Stability | Yalnız açıkça yetkilendirilmiş üretim/review akışı | `approved_ai_media` etiketi ile | Yapay çıktı lisanslı gerçek fotoğraf diye sunulmaz |
| Manual licensed upload | Dosya ve hak sahibi kanıtı | İnsan lisans/attribution onayı | Raw dosya güvenle yeniden encode edilir |

Bir provider’ın environment anahtarının bulunması çağrı izni, katalog lisansı veya otomatik publish izni değildir. Fiyat, kota, provider health ve gerçek çağrı sonuçları admin dışındaki yüzeylere taşınmaz. Yeni provider eklemek, resmi koşullar, ticari kullanım ve gizlilik incelemesi gerektirir.
