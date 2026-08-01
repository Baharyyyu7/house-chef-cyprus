# Media Inventory Audit

Milestone D envanteri, tarif ve blog kayıtlarını public identifier üzerinden raporlar. Envanter çıktısı tarihsel bir iddia değildir; her çalışma anındaki tek kaynak `php artisan media:inventory --json` çıktısıdır.

Her satır; medya durumu, kaynak, fallback, private seçili aday, aday/red sayısı, alt metin/caption/lisans/attribution tamlığı, boyut, MIME, checksum, fidelity, responsive durum ve public/OG/schema/sitemap uygunluğunu taşır. Internal database ID, private URL, provider ham payload veya secret içermez.

`approved_real_media` yalnız insanın tamamladığı lisans ve attribution onayını gösterir. `branded_fallback` görünür bir tasarım fallback’idir; gerçek fotoğraf, schema image veya image sitemap girdisi değildir.

```bash
php artisan media:inventory --json > /secure-ops-path/media-inventory.json
php artisan media:health --json
```

Envanterdeki eksik medya veya belirsiz fidelity, editoryal aday araştırma sırasına girer. Sistem bu satırları otomatik public etmez.
