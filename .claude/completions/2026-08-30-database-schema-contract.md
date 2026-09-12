# Veritabani sema sozlesmesi muhafizi

Tarih: 2026-08-30

## Yapilanlar

- `db:schema-contract` gozlemsel denetim komutu eklendi.
- Migration `up()` metotlarindan N <= 64 kolon sozlesmeleri AST ile
  cikariliyor.
- Uygulamadaki create/update/insert/fill yazma noktalari listeleniyor.
- Sabit deger tasmalari ve siniri kanitlanmamis dinamik yazmalar ayri bulgu
  olarak raporlaniyor.
- Komut basarili cikis kodu veriyor, kosuyu `integrity_reports` tablosuna
  kaydediyor ve panelde Veritabani sozlesmesi karti olarak gorunuyor.
- Komut her gece 01:29 zamanlayicisina eklendi.
- Uretim MySQL snapshoti migration semasiyla salt okunur karsilastirildi.
- Koleksiyon sitemap `lastmod` degeri en son yayinlanan tariften turetiliyor;
  tarih yoksa alan basilmiyor.
- ItemList image karari Google'in birincil dokumanlariyla belgelendi ve
  mevcut davranis bu gorevde degistirilmedi.

## Uretim sema sonucu

- 1.479 uretim kolonu
- 0 eksik kolon
- 0 beklenmeyen kolon
- 0 veri tipi farki
- 0 varchar uzunluk farki

## Dogrulama

- Hedefli paket: 46 test, 107 assertion, basarili.
- Tam paket: 958 test, 5.013 assertion, basarili.
- Gercek depo komutu: 299 dar kolon, 627 yazma noktasi, 229 dinamik
  inceleme riski.
- Pint ve `git diff --check`: basarili.

## Teslim

Commit, push ve deploy yapilmadi. NVIDIA saglayici ve deploy scripti
degisikliklerine dokunulmadi.
