# Changelog

## Technical Foundation C — Security ve Media Temeli

- Merkezi güvenli HTTP başlıkları, CORS allowlist, registration throttle ve
  güvenli local seed admin politikası eklendi.
- SSRF'e karşı SafeUrlValidator ile provider-bağımsız local media storage
  sözleşmesi eklendi.

## Technical Foundation B — Resmî Kapanış

- Cache, queue, scheduler ve database performance kapsamı; baseline ve indeks
  stratejisi dokümantasyonuyla kapatıldı.

## Technical Foundation B — Database Performance

- Sorgu kalıpları için hedefli, taşınabilir indeksler ve merkezî pagination
  yapılandırması eklendi.
- Arama/nutrition/allergen katmanlarında eager-loaded ilişkilerin tekrar
  sorgulanması azaltıldı.
- `performance:profile` komutu ile yerel sorgu/süre baseline ölçümü eklendi.

## Technical Foundation B — Queue/Scheduler

- Queue health komutu, worker heartbeat, stuck/repeated failure görünürlüğü ve
  makine okunabilir JSON çıktısı eklendi.
- Scheduler görevleri heartbeat yaşam döngüsü ve overlap koruması ile kayda
  alındı.
- Yönetici Failed Jobs ekranı; güvenli detay, retry/toplu retry/forget ve audit
  kaydıyla tamamlandı.

## Technical Foundation A — Kontrollü Kapanış

- Nutrition 3B filtre sözleşmesi, Data Quality Engine ve yönetim akışları tamamlandı.
- Data Quality scan yaşam döngüsü, kalite skorları, scheduler kaydı, entity bağlantıları ve yetkilendirme doğrulandı.
- 419/500 hata ekranı tarayıcı regresyonu production öncesi Final QA backlog'una taşındı.

## Recipe Domain Excellence — Sprint 3

- Nutrient, ingredient nutrition fact, cache snapshot ve güven/tamamlanma hesaplama altyapısı eklendi.
- Tarif bazlı alerjen analizi ve belirsizliği koruyan diyet uyumluluğu eklendi.
- Nutrition/allergen/dietary tag API uçları ile tarif detay kartları eklendi.

## Recipe Domain Excellence — Sprint 3A

- Nutrition yönetim ekranı, audit log, snapshot invalidate/recalculate komutları ve otomatik invalidation observer'ları eklendi.
- Alerjen ilişki/override ve dietary threshold veri modeli ile recalculation history altyapısı genişletildi.

## Recipe Domain Excellence — Sprint 1

- Tarif adımları public ULID, sıralama, süre, sıcaklık, timer, opsiyonellik ve rehber alanlarıyla genişletildi.
- Cooking session ve çoklu timer altyapısı eklendi.
- Tarif detayında zengin adım bilgileri ve authenticated yemek yapma modu eklendi.
- Yönetici tarif adımı CRUD ekranları eklendi.
- On iki demo tarif korunarak dört adımlı execution verisiyle güncellendi.
- Import Engine Foundation V1.5 backlog'unda bırakıldı; bu sprint kapsamına alınmadı.

## Recipe Domain Excellence — Sprint 2

- Ingredient alias, substitution ve pantry metadata altyapısı eklendi.
- Açıklanabilir exact/effective recipe matching ve public API recipe-match sözleşmesi eklendi.
- “Elimde bunlar var” ekranı ve ingredient yönetim ekranları eklendi.
# Unreleased

- Added secure admin image upload, re-encoding, EXIF stripping, public/private storage separation and queued thumbnail variants.
- Added media management, safe orphan/temporary maintenance commands, scheduler entries and media health status.
