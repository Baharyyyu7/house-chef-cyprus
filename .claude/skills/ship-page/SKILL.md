---
name: ship-page
description: Brief'ten sayfayı yaz ve güvenle yayınla. page-brief sonrasında, sayfa oluşturma/yeniden yazma/yayınlama isteklerinde kullan.
---

# Sayfa Yayınlama (Diff Gate ile)

Kaynak: Nicholas Dulait / ChatSEO yöntemi

**UYARI:** Bu site Laravel/PHP — içerik `coni:ceviri` ve `coni:serp` komutlarıyla değiştirilir. Doğrudan dosya düzenleme yok; her değişiklik `ConiAction` defterine geçer, geri alınabilir.

## Adımlar

1. `briefs/<keyword-slug>.md` oku. İlk 3'ün ortak bölümlerini + bir fark unsurunu (quiz, hesap makinesi, gerçek porsiyon/süre bilgisi, video) içeren içerik yaz.

2. Yapı:
   - Bir H1: ana keyword içermeli
   - Brief'teki başlık ve meta description
   - Uygun sayfa türü için JSON-LD structured data
   - Bu sayfaya bağlantı veren en ilgili 5 iç sayfadan exact-match anchor ile iç bağlantı

3. **DIFF GATE** — yayınlamadan önce eski versiyonda olup yeni versiyonda EKSİK olan her öğeyi listele:
   - Tablolar, sıralamalar, formlar, quizler, embed'ler, görseller, iç bağlantılar
   - Bu listeyi kullanıcıya göster.
   - **Onay almadan yayınlama.**

4. Onay gelince:
   - Tarif adım/başlık/özet değişikliği: `php artisan coni:ceviri <slug> <dil> <dosya> --neden='...'`
   - Meta başlık/açıklama: `php artisan coni:serp '<adres>' --baslik='...' --aciklama='...' --neden='...'`
   - `git add <sadece değiştirilen dosyalar>` (`git add -A` YASAK — çok ajanlı repo)
   - Commit, push, `scripts/release.sh`

5. Canlıda doğrula: `curl -s <adres> | grep -o '<title>[^<]*'`

## Hatırlatma
Diff gate bir kez bile atlanırsa, o gün sıralama kaybedilir. "Hızlı görünüyor" bahanesi kabul edilmez.
