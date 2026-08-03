# V1.2 doğrulanmış içerik ölçekleme kaydı

## Amaç ve sınırlar

V1.2, House Chef Cyprus kataloğunu 1.000 ayrı ve gerçek editoryal değeri olan tarife doğru güvenli biçimde büyütme hazırlığıdır. Sayısal hedef tek başına yayın veya doğrulama ölçütü değildir. Her tarif; kimlik, ölçü, yöntem, kaynak, SEO, erişilebilirlik ve medya incelemesinden geçmelidir.

Bu kayıt, yerel çalışma veritabanının **3 Ağustos 2026** tarihli salt-okunur anlık görüntüsüdür. Production hesabı, production veritabanı veya yayın durumları için kanıt değildir.

## Yerel başlangıç envanteri

| Alan | Kanıtlanan sayı | Not |
| --- | ---: | --- |
| Tarif | 500 | 50 published, 450 draft |
| Published + verified tarif | 50 | Katalogdan düşürülmez |
| Blog | 200 | 20 published, 180 draft |
| Recipe media ilişkisi | 50 | 50'si `branded_fallback`; gerçek medya değildir |
| Blog hero ilişkisi | 20 | 20'si `branded_fallback`; gerçek medya değildir |
| Duplicate tarif slug | 0 | Teknik benzersizlik kontrolü |
| Duplicate tarif public ID | 0 | Teknik benzersizlik kontrolü |
| Açık content-factory konusu | 2.532 | İnsan/editoryal operasyon backlog'udur |
| Açık critical | 900 | Otomatik kapatılamaz |
| Açık high | 1.222 | Otomatik kapatılamaz |

## V1.2 güvenlik kapıları

- `branded_fallback`, gradient, placeholder veya doğrulanmamış görsel onaylı gerçek medya sayılmaz.
- Media onayı; public görünürlük, placeholder olmama, `verified`, `approved_real_media`, lisans ve attribution onaylarının tümünü gerektirir.
- Yeni içerik; semantik duplicate-family incelemesi, kaynak/ölçü denetimi ve insan onayı olmadan yayınlanmaz veya `verified` yapılmaz.
- Alerjen, saklama, yeniden ısıtma ve beslenme iddiaları yetkili kaynak/insan doğrulaması olmadan kesin ifade edilmez.
- Mevcut 50 published tarif ve 20 published blog için geri alınamaz bulk değişiklik, merge veya medya yayını otomatik yapılmaz.

## Güvenli teknik hazırlık

`content:catalogue-review-readiness --json` yalnız okunur hazırlık özeti üretir. Bu özet V1.2 ölçek hedefi için medya toplulaştırmalarını veritabanında sayar; tüm medya kayıtlarını belleğe yüklemez.

`content:production-audit --json` kalite profillerini tekrar hesaplar, fakat yayınlama/doğrulama yapmaz. Production'da çalıştırılması operasyon sahibinin onayıyla planlanmalıdır.

Blog hero medya hazırlığı, tek bir `verification_status` değerine dayanmaz; ortak `Media::isApprovedRealMedia()` sözleşmesini kullanır. Böylece onaylı sentetik editoryal medya sözleşmeyi sağladığında doğru sınıflanır, eksik lisans/attribution ise yanlışlıkla hazır sayılmaz.

## Sıradaki insan bağımlı işler

1. 50 published tarif için yemek kimliği, ölçü, teknik adım ve kaynak denetiminin editör tarafından kabulü.
2. Her published tarif/blog için hakları ve konu uyumu belgelenmiş gerçek ya da açıkça `SYNTHETIC_APPROVED` medya incelemesi.
3. 900 critical ve 1.222 high konu için kaynak/evidence kararı.
4. Yeni 500 tarif yalnız benzersiz brief, duplicate-family değerlendirmesi ve mutfak editörü onayı sonrasında taslak olarak hazırlanabilir.

## V1.2 yayın durumu

V1.2 bu kayıtla içerik yayınlamaz, mevcut yayın durumunu değiştirmez ve medya onayı üretmez. Katalog ölçekleme için teknik hazırlık yapılmıştır; editoryal, telif ve medya kararları insan onayı beklemektedir.
