---
name: pattern-drip
description: Bir keyword kalıbının bir sonraki sayfasını yayınla. Zamanlı çalıştırmalar veya "kalıbı ölçeklendir / sayfayı çoğalt" isteklerinde kullan.
---

# Kalıp Damlatma (Günde Bir Sayfa)

Kaynak: Nicholas Dulait / ChatSEO yöntemi

**House Chef Cyprus bağlamı:** Tarif kalıpları = `<yemek> + dil`, `<yemek> + teknik`, `<yemek> + şehir/diyaspora`.
Şablon tarif çevirisi yasak — her sayfa gerçek çeviriyle açılır (`RecipeTranslationGate`).

## Adımlar

1. `keywords.md` aç. URL'si olmayan, en yüksek hacimli bir sonraki kalıp keyword'ü al.

2. Bu keyword üzerinde `page-brief` çalıştır. Brief "optimize et" diyorsa → dur, bildir.

3. Bu kalıbın referans sayfasını çoğalt. Sadece şablon değiştirme yok:
   - Yemek adına özgü gerçek bilgiler (malzeme, teknik, kültürel bağlam) güncellenmeli
   - Yalnız şehir/dil adı değişiyorsa sayfa gönderilmez

4. `ship-page` çalıştır. Diff gate referans sayfaya karşı da koşsun: referans sayfada olup bu sayfada eksik olan her şeyi listele.

5. **Bir çalıştırmada bir sayfa. Daha fazlası yok.**

## Zamanlama
Günlük rutine eklemek için:
```bash
# crontab veya Cloudflare Worker ile — siteyi tetikleyen Worker zaten çalışıyor
# Claude Coni 07:20 döngüsünde bu skill'i çağırabilir
```

Yeni bir sitenin 40 sayfayı aynı öğleden sonra yayınlaması otomasyonu ele veriyor.
Bir sayfa. Her gün. Birikmesi altı ay sürer, sonra her yeni sayfa ilk beşe giriyor.
