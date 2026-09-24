# Uretim MySQL - migration sema farki

Tarih: 2026-08-30

## Yontem

Uretim veritabani salt okunur olarak `information_schema.COLUMNS` uzerinden
snapshotlandi. Snapshot yalniz tablo, kolon, veri tipi ve karakter azami
uzunlugunu icerdi; veritabani kimligi, parola veya satir verisi alinmadi.

Yerel tarafta migration dosyalarinin yalniz `up()` metotlari AST ile okunarak
beklenen sema olusturuldu. `db:schema-contract --schema-json=...` ile su uc
fark sinifi karsilastirildi:

- migration'da olup uretimde olmayan kolon,
- uretimde olup migration'da olmayan kolon,
- farkli veri tipi (MariaDB'nin JSON -> LONGTEXT ve INTEGER -> INT
  esdegerlikleri normalize edilerek),
- karakter kolonunda farkli azami uzunluk.

## Sonuc

- Uretim snapshot kolon sayisi: **1.479**
- Migration'da olup uretimde olmayan kolon: **0**
- Uretimde olup migration'da olmayan kolon: **0**
- Farkli veri tipi: **0**
- Farkli karakter uzunlugu: **0**

Bugunku karsilastirmada elle eklenmis kolon, kacmis migration veya farkli
varchar uzunlugu bulunmadi.

## Surekli muhafiz sonucu

Ayni kosuda statik yazma sozlesmesi:

- N <= 64 dar kolon: **299**
- Uygulama yazma noktasi: **627**
- Siniri koddan kanitlanamayan dinamik yazma / sabit tasma adayi: **229**

Bu 229 kayit sema farki degildir. SQLite'in uygulamadigi varchar sinirlarina
yazilan dinamik degerler icin inceleme kuyrugudur. Komut gozlemseldir ve
bulgu olsa da basarili cikis kodu verir; sonuc `integrity_reports` gecmisine
`db:schema-contract` muhafizi olarak kaydedilir ve panelde gorunur.

Snapshot ve tam JSON rapor gecici `/tmp` dosyalarinda olusturuldu; depoya veya
yerel arsive alinmadi.
