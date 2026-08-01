# Content Factory Phase 4 QA

## Teknik kabul kapsamı

- İdempotent planlama 2.340 açık fact-check konusunu **48** review batch'ine yerleştirir; hiçbir batch 50 konuyu aşmaz.
- Kaynak araştırma kuyruğu 2.340 kayıt üretir. Genel triage evidence-pack'i kaynak kabul edilmez; yalnız gerçek reviewer tarafından kaydedilmiş kısa referans/özet kaydı `evidence_ready` olabilir.
- 450 recipe draft ve 180 blog draft için toplam 630 read-only editoryal review paketi hazırlanır. Başlangıçta tümü blokludur; final onay, verified, scheduled ve published değerleri otomatik değişmez.
- Gerçek medya adayları onaylanmadıkça branded fallback korunur ve 630 paket medya insan seçimi bekler.
- Admin, fact-check reviewer, content editor, SEO reviewer ve media reviewer Gate kurallarıyla ayrılır. Member bütün Editoryal Operasyonlar rotalarında 403 alır; seed/system reviewer ataması reddedilir.
- `content:editorial-operations-brief --json` sabit alanlarla operasyon metriklerini döndürür ve `auto_publish:false` ile `human_approval_required:true` bilgisini taşır.

## Yerel doğrulama

1 Ağustos 2026 yerel doğrulamasında `migrate:fresh --seed`, ikinci seed, triage, plan ve Morning Brief V2 çalıştı. Sayılar: 900 critical, 1.080 high, 180 medium, 180 low; 50 published/verified tarif, 20 published blog, 450 recipe draft ve 180 blog draft korundu.

Feature testi operasyon batch sınırı/idempotency'si, source evidence koruması, review bölüm audit'i, gerçek reviewer ataması, member 403 ve Morning Brief'i kapsar. Tam PHPUnit paketi 271 geçti / 1 güvenli skip ile başarılıdır.

## Tarayıcı QA

Local admin oturumunda dashboard, batch, source queue, blocked review-packs ve dark mode kontrol edildi. 375/768/1024/1440 viewportlarında ana doküman yatay taşma üretmedi; tablolar kendi yatay kaydırma kapsayıcılarında kaldı. Local member fixture'ı aynı admin rotasında erişim engeli ekranını aldı.

İnsan kaynak değerlendirmesi, gerçek editorial kararlar ve public yayın onayı teknik QA'nın dışında kalan operasyonel iştir; bu kontroller auto-publish ile atlanamaz.
