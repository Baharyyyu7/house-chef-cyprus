# İndekslenebilir Tarif Koleksiyonları

## Tamamlananlar

- `/sure/20-dakika` ve `/sure/30-dakika` süre koleksiyonları eklendi.
- `/zorluk/kolay`, `/zorluk/orta` ve `/zorluk/zor` koleksiyonları eklendi.
- Sayfalar yalnız en az dört `published` ve `verified` tarif olduğunda açılıyor.
- Landing sayfaları ile pages sitemap aynı `sayfasiOlanlar()` kaynağını kullanıyor.
- Tarif detayındaki mutfak bağlantısı, yeterli tarif varsa mutfak sayfasına; yoksa filtreli kataloğa gidiyor.
- Tarif BreadcrumbList sırası `Ana Sayfa > Mutfaklar > Mutfak > Tarif` olarak güncellendi.
- Mevcut `recipes:normalize-difficulty` komutunun varsayılan dry-run güvenliği ve kanonik `easy|medium|hard` modeli korundu.

## Doğrulama

- TDD kırmızı koşusu: yeni landing ve sitemap testleri eksik rotalar nedeniyle beklendiği şekilde başarısız oldu.
- Odaklı paket: 34 test, 98 assertion — başarılı.
- Tam paket: `ALLOW_DIRTY_BUILD=1 php artisan test` — 933 test, 4944 assertion — başarılı.
- `ALLOW_DIRTY_BUILD=1` yalnız çalışma ağacındaki paralel ajan değişikliklerinin artifact dry-run korumasını bilinçli test etmek için kullanıldı; deploy yapılmadı.
- Pint hedef dosyalarda uygulandı; `git diff --check` temiz.
