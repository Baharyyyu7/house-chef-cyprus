# Çeviri Üretimi - Codex ve Antigravity

Sitenin arayüzü dört dilde hazır ama **içeriği yalnız Türkçe**: 181 tarif,
27 yazı. Bu belge içerik çevirisinin nereye, nasıl teslim edileceğini
tanımlar.

Diğer görevler: [görsel](GOREV_ANTIGRAVITY_GORSEL.md) ·
[tarif kayıtları](GOREV_CODEX_TARIF_KAYITLARI.md) ·
[SEO blog](GOREV_SEO_BLOG_URETIMI.md)

---

## 0. Neden bu kadar dikkatli

Dili açıp içeriği Türkçe bırakmak siteyi dünyaya açmaz, **zayıflatır.**

Google bir sayfanın dilini görünen içerikten belirler, menüsünden değil.
İngilizce menülerin altında Türkçe tarif duruyorsa Google o sayfayı Türkçe
sayar ve aynı içeriğin ikinci bir kopyası olarak görür. Üstelik yardımcı
içerik sistemi **site geneli** çalışıyor: çok sayıda zayıf dil sayfası,
güçlü Türkçe sayfaların sıralamasını da aşağı çeker.

Bu yüzden sistem şu kuralı kodla uyguluyor:

> **Çevirisi olmayan kayıt o dilin sitesinde yoktur.**

Çevirisi olmayan tarif listede görünmez, sitemap'e girmez, hreflang
kümesinde yer almaz. Sessizce Türkçeye düşmek yok.

**Kısmi çeviri de yeterli değil.** Bir tarifin `title`, `excerpt` ve
`description` alanlarının üçü birden çevrilmemişse o tarif çevrilmemiş
sayılır. Başlığı İngilizce, açıklaması Türkçe bir sayfa tam olarak
kaçınmaya çalıştığımız şeydir.

---

## 1. Teslim yeri

```
~/domains/housechefcyprus.com/shared/storage/app/content-import/translations/{locale}/{tur}-{slug}.json
```

Örnek:

```
.../translations/en/recipe-hellim-izgara.json
.../translations/pl/blog-corba-kivami-nasil-ayarlanir-Y20XY.json
```

Sunucuda, yerelde değil. Bu dizin yazılabilir olduğu için **yalnız JSON**
kabul edilir; `.php` dosyaları asla çalıştırılmaz ya da içe alınmaz.

---

## 2. Dosya biçimi

```json
{
  "type": "recipe",
  "slug": "hellim-izgara",
  "locale": "en",
  "fields": {
    "title": "Grilled Halloumi That Never Falls Apart",
    "excerpt": "Why halloumi splits on the grill and how to stop it.",
    "description": "Temperature, timing and the one thing most guides skip."
  },
  "translator_note": "Başlık birebir çeviri değil; İngilizce arama niyetine göre yeniden yazıldı."
}
```

Zorunlu alanlar tür başına:

| Tür | Çevrilecek alanlar |
|---|---|
| `recipe` | `title`, `excerpt`, `description` |
| `blog` | `title`, `excerpt`, `body` |

Üçü de dolu olmayan dosya reddedilir.

---

## 3. Çeviri değil, yerelleştirme

En sık yapılan hata Türkçe cümleyi kelimesi kelimesine çevirmek. Sonuç
dilbilgisi olarak doğru ama **o dilde kimsenin aramadığı** bir başlık olur.

Kural: **başlık ve özet, hedef dilin gerçek arama sorgusundan yeniden
yazılır.** Gövde çevrilir, başlık yeniden kurulur.

| Türkçe | Yanlış (birebir) | Doğru (niyet) |
|---|---|---|
| Hellim Izgara | Halloumi Grill | Grilled Halloumi: Why It Splits and How to Stop It |
| Kıbrıs Kebabı | Cyprus Kebab | Sheftalia: The Cypriot Sausage You Grill Whole |

Her dosyada `translator_note` alanına başlığı neden öyle kurduğunu yaz.

**Ölçüm bekliyorum:** hedef dilde o ifadenin gerçekten arandığına dair bir
işaret (autocomplete, "people also ask", Trends). Ölçmediğin başlığı yazma.

---

## 4. Diller ve sıra

Hedef: **tr, en, pl, fa**. Türkçe kaynak dildir, çevrilmez.

**Aynı anda dört dile girme.** Bir dili tam bitir, sonuçlarını ölç, sonra
diğerine geç. Dört dili yarım açmak, bir dili tam açmaktan hem daha pahalı
hem daha riskli.

Önerilen sıra:

1. **`en`** - arayüzü hazır, turist niyeti en yüksek hacimli damar
2. **`pl`** - Kıbrıs'a charter turizmi var, İngilizceye göre rekabet düşük
3. **`fa`** - bölgesel okuyucu

**Lehçe için ek iş var:** `lang/pl/` dizini ve `lang/pl.json` hiç yok.
Arayüzün 477 anahtarı çevrilmeden Lehçe içerik üretmenin anlamı yok.
Sırası: önce arayüz, sonra içerik.

---

## 5. Neyi çevireceğini seçme

**181 tarifin tamamını çevirme.** Search Console'da gösterim alan ilk 20
tarifle başla: Google zaten onları biliyor, çeviri o bilinirliği yeni dile
taşır.

Sıfır gösterimi olan bir tarifin çevirisi sıfır trafik getirir ve bakım
yükü olarak kalır.

Blog tarafında da aynı: en çok gösterim alan yazılar önce.

---

## 6. Kalite eşiği

- [ ] Türün bütün zorunlu alanları dolu
- [ ] Başlık birebir çeviri değil, hedef dilin sorgusundan kurulmuş
- [ ] `translator_note` başlığın gerekçesini içeriyor
- [ ] Ölçü birimleri hedef pazara uygun (gram/ml korunur, `su bardağı` gibi
      yerel ölçüler açıklanır)
- [ ] Malzeme adları hedef dilde gerçekten kullanılan karşılıklar
      (`hellim` → `halloumi`, `pul biber` → `Aleppo pepper`)
- [ ] Kültürel not düşürülmemiş: yemeğin nereden geldiği bilgisi çeviride
      kaybolmaz, bu sitenin farkı
- [ ] Makine çevirisi çıktısı olduğu gibi bırakılmamış

Son madde ciddi. Google'ın 2025 duruşuna göre yapay zekâ çevirisi
kendiliğinden spam değil - ama **ölçekli düşük değerli çeviri** "scaled
content abuse" kapsamına giriyor. Elden geçmemiş makine çıktısı tam olarak
budur.

---

## 7. Kesinlikle yapılmayacaklar

- **Dili etkinleştirme.** `config/locales.php` içindeki `enabled` alanına
  dokunma. Dil, içerik hazır olunca insan tarafından açılır.
- **Kısmi çeviri teslim etme.** Eksik alanlı dosya kaydı yarım bırakır.
- **Türkçe metni olduğu gibi kopyalama.** Alan doluymuş gibi görünür ama
  sayfa Türkçe kalır - engellemeye çalıştığımız durumun ta kendisi.
- **PHP veya çalıştırılabilir dosya bırakma.**
- **Depoya (yerel diske) yazma.**

---

## 8. Teslim sonrası

```bash
php artisan content:import-translations            # rapor, hiçbir şey yazmaz
php artisan content:import-translations --apply    # çevirileri yazar
```

Raporla:
- kaç kayıt, hangi dilde
- her başlığın gerekçesi
- hangi ölçüme dayanarak seçildi

**Dili açma. Rapor et, dur.**
