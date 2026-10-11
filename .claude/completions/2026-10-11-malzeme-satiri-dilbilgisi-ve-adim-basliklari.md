# 2026-10-11 Malzeme satırı dilbilgisi, eksik katalog malzemesi, TR adım başlıkları

Canlı: 6a86487 (tam paket 1855/1855).

## Kod
- 733b7f9 HouseholdMeasurementFormatter::yabanciSatir: ru/pl adı başa alır (hal gerekmez), pt ölçüden sonra "de". pl adet "szt.", pt-BR birim adları maço/pacote/punhado.
- 6a86487 coni:malzeme: Türkçe ad anahtarı katalogda olmayan malzemeyi açar; geri al kullanılmıyorsa siler. RUNBOOK 2c.

## Canlı veri (geri alma sırası tersten)
- coni:kaynak 30639: 95 tarif, 456 TR adım başlığı + 5 adım dilbilgisi.
- coni:malzeme-ekle 30638: peynirli-borek çörek otu, coban-salata sumak (isteğe bağlı), kekli-supangle kek 100 g (isteğe bağlı), mercimek-koftesi marul.
- coni:malzeme 30637: #260-263 açıldı, 24 ad.

## Doğrulama
- izmir-sambali ve 4 tarif x 7 dil: 200, index,follow, satırlar beklenen biçimde.
- Açık yabancı tarif sayfası 738 (dil başına 123); canlıda kalan "Adım N" yalnız sucuklu-yumurta.

## Kalan
- sucuklu-yumurta şablon adımlar (kullanıcı kararı).
- de satırında sıfat büyük harf; katalogda "(Şerbet için)" ekli malzemeler.
- 35 tek kelimelik TR adım başlığı, 18 eski biçimli EN başlık (kullanıcı kararı).
