# Açık işler

Uzun koşular sırasında bağlam özetleniyor; bu dosya o özetten sağ çıkıyor.
Sohbet geçmişi değil, burası okunur. İlerledikçe güncellenir.

**Son ölçüm: 2026-09-24**

## Bende değil, kullanıcıda

- [ ] **GPTBot engeli** - Hostinger hPanel → CDN. Destek talebi açıldı,
      mühendise iletildi, yanıt bekleniyor. Site Bing'de sıfır görünürlükte.
- [ ] **22 indeksli tarif görselsiz** (403 yayındaki toplam). Gerçek
      fotoğraf gerekiyor. Uydurma görsel (AI/stok) yasak - sitenin
      geçmişinde sahte medya olayı var. İçerikleri sağlam: 403'ün tamamı
      tarif kalite kapısından geçiyor, eksik olan yalnız fotoğraf.
      Flaouna, Kolokotisi, Şeftali Kebabı, Paluze, Tarhana Çorbası gibi
      gerçek Kıbrıs yemekleri.
- [ ] **Ses malzemesi.** Yazıları "doğru bilgi"den "sadece bu sitede olan
      bilgi"ye çevirmek için: yaşanmış 5-10 Kıbrıs mutfak olayı, gerçek
      sayılar (kaç yıl, kaç etkinlik), tartışmalı mutfak görüşleri.
      Bunlar olmadan da devam ediliyor, sonuç rakiplerle aynı seviyede kalıyor.

## Sırada

- [ ] **Alakasız malzeme ataması.** Ölçü dönüşümünü incelerken çıktı:
      "Yoğurtlu Yulaf Kasesi" tarifinde 3 adet tavuk yazıyor. Zeytinli
      açma, sebzeli kiş, zaatarlı pide dilimleri de aynı durumda. Şablon
      üretimi malzemeleri alakasız tariflere bağlamış. 471 şablon adlı
      tarif taslağa alındı ama bunların adı bir yemeği tarif ettiği için
      o temizlikte kapsam dışında kaldılar. Ölçülmedi, ayrı iş.

- [ ] **590 başlık çevrilmemiş.** İndekslenebilir sayfalarda başlık hâlâ
      Türkçe metni içinde taşıyor, sonuna çevrilmiş kuyruk eklenmiş.
      Dağılım: de 108, fa/pl/pt 109, ru 109, en 46.
      **Öncelik ru**: 339 gösterim, 1 tıklama, %0.29 CTR.
      Yeni girişleri kapı durduruyor (commit f3f9282), mevcut borç duruyor.
- [ ] **110 şablon yazı.** Gövde 3000-3500 karakterde sıkışan makine partisi,
      emoji başlıklı ("🔍 1. Tarihsel Arka Plan..."). Reklamları kalktı
      (commit 52e599f) ama içerik hâlâ ince. Gösterim alanlardan başla.
- [ ] **Arşivden 15 yazı yayınlanmadı.** Her biri Türkçe gövde + İngilizce
      çeviri istiyor, ikisi de Markdown'a çevrilmiş olmalı.
      Kalanlar: turkish-breakfast-kahvalti-guide, turkish-eggs-cilbir-guide,
      menemen-vs-shakshuka, types-of-turkish-kebab, turkish-flatbreads-guide,
      turkish-desserts-guide, baklava-types-storage-guide,
      kunefe-cheese-substitutes, types-of-turkish-borek, turkish-soups-guide,
      vegan-vegetarian-turkish-cypriot-food, pomegranate-molasses-guide,
      aleppo-pepper-pul-biber-urfa-biber, pumpkin-recipes-cyprus-turkey,
      turkish-salads-guide
- [ ] `/en/blog/types-of-turkish-kebab` kırık iç bağlantı - o yazı
      yayınlanınca kendiliğinden kapanır.

## Bitti (bu tur)

- [x] Dil dil büyüme ölçümü ölü tabloya bağlıydı (`46e39cb`) - yedi dilde de
      "ölçüm yok" yazıyordu, artık canlı Search Console okuyor.
- [x] Kaynağı içinde taşıyan başlık çeviri sayılmıyor (`f3f9282`).
- [x] Yayın test kapısı yanlış sebeple kapanıyordu (`9d86237`) -
      `php artisan test` SIGTERM alıyor, doğrudan phpunit çağrılıyor.
- [x] Kapya biber yazısı: güvensiz konserve tarifi, 7 dilde birden canlıydı.
      Yeniden yazıldı, yedi dilde de değiştirildi, doğrulandı.
- [x] İnce yazı reklam taşımıyor (`52e599f`) - 110 yazının reklamı kalktı,
      yükleyici betiği de aynı kapıya bağlandı (otomatik reklamlar yuvaya
      bakmadan yerleştiriyor).
- [x] Yönetim panelindeki "Dikkat gerekenler" 5 maddesinden 4'ü kapandı
      (veri düzeltmesi, kod değil). Yedek:
      `shared/storage/app/yedekler/panel-duzeltme-yedek-20260924-123644.json`
      - 9 mükerrer medya kaydı silindi (aynı tarife aynı dosya).
      - 471 şablon adlı tarif taslağa alındı ("Fırın", "Acılı", "Peynirli"
        bir yemek adı değil; kodun kendi yorumu bunlara scaled content
        diyor). Yayındaki tarif 1007 → 536.
      - 13 ölçü dönüşümü yöntemi yazılarak doğrulandı (su bardağı 200 ml,
        yemek kaşığı 15 ml; yoğunluk hesabı). "Tavuk 1 adet → 180 g"
        doğrulanmadı, kapatıldı: bütün tavuk 1200-1500 g, bir parça
        150-200 g, tek sayı yazılamaz.
      - Mantar besin değerleri düzeltildi. `origin=ai_estimated` idi ve
        100 g'da 160 kcal yazıyordu; doğrusu 22 kcal. Sekiz değerin hepsi
        çiğ kültür mantarı referansıyla değiştirildi.

## Ölçümler

| | tıklama | gösterim | CTR | sıra |
|---|---|---|---|---|
| tr | 31 | 2688 | 1.15% | 9.9 |
| en | 8 | 952 | 0.84% | 11.4 |
| de | 5 | 736 | 0.68% | 9.3 |
| fa | 8 | 128 | 6.25% | 6.6 |
| pl | 4 | 133 | 3.01% | 8.9 |
| ru | 1 | 339 | 0.29% | 8.3 |
| pt | 2 | 131 | 1.53% | 11.2 |

Toplam 59 tıklama / 5107 gösterim. Önceki tur 51 / 4323.

Yayındaki 156 yazının gövde dağılımı çift tepeli: ortanca 3375 karakter,
4000 üstünde 46 gerçek makale, geri kalanı makine partisi.

## Ortam tuzakları

- `php artisan test` bu makinede SIGTERM alıyor. `php vendor/bin/phpunit`
  kullan. `--parallel` yok (paratest kurulu değil), `--log-json` yok
  (PHPUnit 12), `--log-junit` var.
- Tam paket burada duvar saatiyle çok yavaş (CPU'su düşük). Değişikliğin
  dokunduğu dosyaları `grep -rln` ile bul, yalnız onları kapsayan test
  dosyalarını koş; tam paketi `release.sh` temiz kopyada koşsun.
- Autoloader classmap başka bir worktree'yi gösterebiliyor. Şüphelenirsen:
  `php -r 'require "vendor/autoload.php"; echo (new ReflectionClass("App\\Services\\Seo\\LocaleGrowthService"))->getFileName();'`
  Bu depo çıkmıyorsa `composer dump-autoload`.
