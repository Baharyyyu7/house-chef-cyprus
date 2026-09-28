---
name: money-keywords
description: House Chef Cyprus için ticari amaçlı anahtar kelime kalıplarını bul. "Hangi anahtar kelimeleri hedefleyelim", "SEO stratejisi" veya "para nereden gelir" sorularında kullan.
---

# Para Getirecek Anahtar Kelimeler

Kaynak: Nicholas Dulait / ChatSEO yöntemi — sadece alıcı niyeti taşıyan sorgular hedeflenir.

## Hedef kriter
- Arama sonuçlarında tarif/kategori/ürün sayfaları ilk 3'te → ticari niyet → hedefle
- Blog yazıları ve bilgi kutuları ilk 3'te → bilgi amaçlı → şimdi geç

## Adımlar

1. `php artisan coni:durum --json` çalıştır. Her dil için `adaylar` ve `tiklanmayan` listesini al.
2. Her dil için Google'da `<yemek adı> tarifi <şehir/diyaspora>` kalıplarını kontrol et:
   - İlk 3 sonuç tarif/kategori sayfasıysa → ticari niyet var
   - Sorgu kalıpları: `<yemek> nasıl yapılır`, `<yemek> Rezept`, `<yemek> recipe`, `<yemek> рецепт`
3. Kalıp grupları: yemek adı + dil, yemek adı + teknik (nasıl yapılır, how to make), yemek adı + diyet (vegan, glutensiz)
4. ChatSEO MCP bağlıysa: `send_message` ile siteId için strateji sor, `get_conversation_messages` ile bekle.
5. `keywords.md` dosyasına yaz: kalıp, anahtar kelime, tahmini hacim, Google'ın sıraladığı sayfa türü, elimizdeki URL var mı.
6. **Hacim uydurma. ChatSEO veya `coni:durum` vermemişse "n/a" yaz.**

## Çıktı
`keywords.md` — kalıp, keyword, hacim (n/a ise n/a), sayfa türü, mevcut URL
