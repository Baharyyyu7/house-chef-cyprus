# 2026-10-11 Çeviri sadakat denetimi ve TR kaynak düzeltmeleri

## Yapılan
- Önceden açık 218 tarif x dil sayfası adım adım Türkçe asla karşılaştırıldı (workflow wf_e48ef093-b13, 34 ajan; denetçi + bağımsız doğrulayıcı). Uydurma içerik (ör. mercimek çorbasında paprikalı tereyağı), düşen süre/ölçü, yanlış malzeme düzeltildi.
- pt malzeme sözlüğü Brezilya Portekizcesine çekildi (96 ad, coni:malzeme rapor #30629); 123 pt sayfasının metni buna hizalandı.
- Canlıya: coni:ceviri-toplu --zorla 218 çift, 0 hata.
- Yeni komut coni:kaynak (9b946f7): TR alanını yerinde yazar, geri alınır. content:import-recipes 6 dilin çevirisini sildiği için gerekliydi.
- 34 tarifte 49 TR çelişki düzeltildi (rapor #30630), aynı değişiklik 181 dil çiftine taşındı (workflow wf_561bd311-178, 16 ajan).

## Doğrulama
- Tam test paketi 1849/1849 (9b946f7). /health 9b946f7.
- Canlı yeniden dışa aktarım: yazılan her alan dosyayla birebir; açık sayfa 738 (dil başına 123).
- Canlı HTML: düzeltilmiş metinler ve yeni H1'ler görünüyor; örnek sayfalar 200, doğru lang, index,follow, kanonik kendisi.

## Açık
- Malzeme listesi eksikleri (~30 tarif) için liste düzenleyen araç yok.
- pt blog yazı başlıkları Avrupa Portekizcesi.
- 35 tek kelimelik adım başlığı ve 18 eski biçimli EN başlık karar bekliyor.

## Geri alma
- php artisan coni:kaynak --geri-al=30630
- php artisan coni:malzeme --geri-al=30629
- Çeviriler: coni_actions defteri (reason "Sadakat..." 218 kayıt, "TR kaynak celiskisi..." 181 kayıt), coni:geri-al <id>
