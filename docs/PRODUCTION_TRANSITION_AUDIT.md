# Production Transition Audit

## Kapsam

`app:production-language-audit --json`, public ve admin Blade/Livewire arayüzlerinde beta, demo, MVP ve benzeri kullanıcıya görünen ifadeleri denetler. Test fixture, geçmiş changelog, migration ve yerel teknik adlandırmalar bu taramanın dışında tutulur.

## Güvenli istisnalar

- Local/testing seed hesapları yalnız teknik geliştirme verisidir.
- `branded_fallback`, eksik gerçek medya için dürüst bir durum adıdır; gerçek fotoğraf anlamına gelmez.
- Tarihsel dokümanlar ve CHANGELOG geçmişi geriye dönük değiştirilmez.

## Sonuçların kullanımı

Bulgu oluşursa kullanıcıya görünen metin production diliyle düzeltilir veya eksik özellik dürüst empty-state ile sunulur. Bulgu kapatılması içerik onayı veya yayın işlemi değildir.

## Public AI yüzeyi

Tarif sayfalarındaki geçici AI yanıt yüzeyi production rotalarından kaldırılmıştır. Mevcut sağlayıcı sözleşmesi ileride gerçek sağlayıcı, güvenlik, maliyet ve insan onayı gereklilikleri karşılandığında değerlendirilebilir; kullanıcıya örnek veya temsili yanıt sunulmaz.
