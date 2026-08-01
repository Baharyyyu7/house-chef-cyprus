# Milestone D — Real Media Excellence

Milestone D, medya kaynaklarının gerçeklik, lisans, tarif-fidelity ve public kullanım sınırlarını tek operasyon sözleşmesinde toplar. Bu belge bir medya onayı veya katalog kapsama iddiası değildir; canlı envanter yalnız komutların gerçek çıktısından okunur.

## Public yayın kapısı

Bir görselin recipe/article schema, Open Graph veya image sitemap içinde görünmesi için aşağıdaki koşulların tamamı gerekir:

1. `visibility=public`
2. `verification_status=verified`
3. `media_status=approved_real_media`
4. `license_review_status=approved`
5. `attribution_review_status=approved`
6. Placeholder olmaması

Private adaylar, branded fallback, eksik lisans/attribution ve yapay üretim bu kapıyı geçmez. Yapay üretim onaylanmış olsa bile `approved_ai_media` durumuyla etiketlenir; lisanslı gerçek fotoğraf olarak sunulmaz.

## Güvenli sağlayıcı akışı

Pexels ve Unsplash adayları önce metadata veya private dosya olarak alınır. Aday taraması; download, public etme, schema ekleme veya otomatik onay işlemi yapmaz. Remote indirme yalnız HTTPS allowlist üzerinden, redirect kapalı, boyut sınırı ve içerik imzası kontrolüyle yapılır. İndirilen içerik yeniden encode edilir; raw provider payload, token veya stack trace admin/public yüzeylere yazılmaz.

İnsan incelemesi; tarif-fidelity, lisans, attribution, alt metin, dosya kalitesi ve editoryal bağlam kararını kapsar. Red veya belirsizlik branded fallback’i değiştirmez.

## Operasyon komutları

```bash
php artisan media:inventory --json
php artisan media:candidate-scan --provider=pexels --dry-run --json
php artisan media:fidelity-scan --dry-run --json
php artisan media:license-audit --dry-run --json
php artisan media:attribution-audit --dry-run --json
php artisan media:duplicate-scan --dry-run --json
php artisan media:optimize --dry-run --json
php artisan media:health --json
php artisan media:morning-brief --json
```

Mutasyon yapan komutlar küçük batch sınırlarına uyar. `--dry-run` hiçbir kayıt veya dosya değiştirmez. Fidelity/lisans/attribution taramaları karar desteğidir; hiçbir zaman insan onayı üretmez.

## Varyasyonlar ve teslim

Onaylı, local ve gerçek görseller için `media:optimize`; JPEG zorunlu, WebP/AVIF ise sunucuda destek varsa ek varyasyonlar üretir. Varyasyonlar idempotenttir, responsive metadata’da tutulur ve mevcut storage/CDN köküne uyumludur. Unsplash hotlink görselleri provider-managed olarak kalır; dosya kopyalanmaz.

## Rol sınırı

Medya operasyonları admin veya explicit `media_reviewer` rolüyle açılır. Final gerçek medya onayı varsayılan olarak admin gerektirir; reviewer final onayı yalnız merkezi ayar açık olduğunda kullanabilir. System/seed kullanıcıları review aktörü olamaz.

## Operasyonel backlog

Gerçek kapsama yüzdesi, her tarif/blog için kaynak araştırması ve insan review ile artar. Bu sistem eksik gerçek medyayı otomatik üretmez, stok görseli tarifle eşleştirilmiş varsaymaz veya public görünürlüğü otomatik açmaz.
