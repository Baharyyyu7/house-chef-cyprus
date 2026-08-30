# ItemList gorsel tutarliligi karari

Tarih: 2026-08-30

## Soru

Koleksiyon `ItemList` semasinda bazi tariflerde `image` bulunup bazilarinda
bulunmamasi Google acisindan tutarsizlik midir?

## Birincil kaynaklar

- [Google Carousel ItemList dokumani](https://developers.google.com/search/docs/appearance/structured-data/carousel)
- [Google Recipe structured data dokumani](https://developers.google.com/search/docs/appearance/structured-data/recipe)

## Bulgular

Bu sitedeki koleksiyonlar birer **ozet sayfadir**: kartlar ayri tarif detay
sayfalarina baglanir. Google ozet sayfasi `ListItem` kaydinda yalniz su uc
alani ister:

- `@type: ListItem`
- `position`
- `url`

Google `item`, `name`, `image`, `prepTime` gibi tarif alanlarini ayni sayfada
tam tariflerin yer aldigi **all-in-one page** bicimi icin tanimliyor. O
bicimde `item.url` ayni sayfadaki bir anchor'a gitmelidir. Bizim kartlarimiz
ise ayri detay sayfalarina gider.

Ozet sayfasinda asil tutarlilik kosulu her gorunen kartin ayni sirayla listede
bulunmasi ve her detay URL'sinin kendi sayfasinda gecerli `Recipe` semasi
tasimasidir. `image` tutarliligi ozet `ItemList` icinde cozulecek bir konu
degildir.

## Karar

Dogru nihai bicim, koleksiyonlardaki `ListItem` kayitlarini `position + url`
ile sinirlamak ve gorsel/Recipe alanlarini detay sayfalarina birakmaktir. Bu,
16 kartin 11'inde image bulunmasi sorununu sahte veya alakasiz gorsel
eklemeden ortadan kaldirir.

Bu gorevde mevcut karma `image` davranisi degistirilmedi. Isaretleme bicimi
ayri bir degisiklikte Rich Results Test ile birkac canli URL uzerinde
dogrulanarak ozet sayfasi formatina gecirilmelidir. Onaysiz gorsel schema'ya
eklenmeyecek kural aynen korunmalidir.
