# 🍽️ Görsel Üretim Dağıtık Koordinasyon ve Canlı Kilit Sistemi

> **Canlı İlerleme:** `%2.2` Tamamlandı | **Tamamlanan:** `8` / `366` | **İşleniyor:** `1` | **Boşta:** `352`
> **Son Güncelleme:** 2026-09-18 06:58:27
> **Kurallar:** [docs/GORSEL_URETIM_MASTER_PROMPT.md](GORSEL_URETIM_MASTER_PROMPT.md) ve [docs/AJAN_CALISMA_KURALLARI.md](AJAN_CALISMA_KURALLARI.md)

## 1. Dağıtık Kilit Protokolü ("Ben Yapacağım" Mekanizması)
Birden fazla yapay zeka ajanının aynı anda güvenle çalışabilmesi için:
1. **İş Alma (Claim):** `php scripts/image-queue.php claim --worker="AI-Worker-1"`
   - Sistem boştaki ilk yemeği seçer, kilidi o ajana verir ve durumu `claimed` yapar.
   - Diğer hiçbir ajan bu yemeği alamaz; diğer ajanlar bir sonraki boştaki yemeği alır.
2. **Üretim ve Yükleme:** Ajan görseli üretir, `scripts/upload-recipe-image.sh` ile 1920x1620 PNG olarak sunucuya aktarır.
3. **Tamamlama (Complete):** `php scripts/image-queue.php complete --slug="{slug}" --worker="AI-Worker-1"`
   - Durum `completed` olarak kaydedilir ve ajana yeni iş verilir.
4. **Zaman Aşımı / Hata (Timeout):** Kilitler otomatik dağıtılmaz. Aynı worker claim ile işine devam eder; renew ile zamanı yeniler. release ve block için sahip worker zorunludur.

Engellenen kayıtlar: `5`. `block --slug=... --worker=... --reason=...` ile eksik kimlik açıklanır.

## 2. Canlı Kuyruk Listesi

| # | Durum | Kilitleyen Ajan | Tarif Başlığı | Mutfak | Kategori | Dosya Adı (`media-import/recipes/`) |
|:---:|:---:|:---|:---|:---|:---|:---|
| 1 | `! Engelli` | - | **Kıbrıs Usulü Fırın (Tarif başlığı eksik; hangi fırın yemeği olduğu doğrulanamıyor.)** | Kıbrıs Mutfağı | Ana Yemek | `recipe--kibris-usulu-firin-YDQEZ.png` |
| 2 | `! Engelli` | - | **Kıbrıs Usulü Fırın (Tarif başlığı eksik; hangi fırın yemeği olduğu doğrulanamıyor.)** | Kıbrıs Mutfağı | Ana Yemek | `recipe--kibris-usulu-firin-4ST5Z.png` |
| 3 | `✓ Bitti` | **AI-Worker-1** | **Kıbrıs Usulü Fırın Tavuk Baget** | Kıbrıs Mutfağı | Ana Yemek | `recipe--kibris-usulu-firin-tavuk-baget-PCR3Q.png` |
| 4 | `✓ Bitti` | **Antigravity-Master** | **Kıbrıs Usulü Kleftiko (Kuzu Fırın)** | Kıbrıs Mutfağı | Ana Yemek | `recipe--kibris-usulu-kleftiko-kuzu-firin-JSCP5.png` |
| 5 | `✓ Bitti` | **AI-Worker-4** | **Kıbrıs Usulü Kleftiko (Kuzu Fırın)** | Kıbrıs Mutfağı | Ana Yemek | `recipe--kibris-usulu-kleftiko-kuzu-firin-63QYN.png` |
| 6 | `⏳ İşleniyor` | **AI-Worker-4** | **Kıbrıs Usulü Molohiya** | Kıbrıs Mutfağı | Ana Yemek | `recipe--kibris-usulu-molohiya-0TZW4.png` |
| 7 | `✓ Bitti` | **Automation-Images-20260916** | **Kıbrıs Usulü Molohiya** | Kıbrıs Mutfağı | Ana Yemek | `recipe--kibris-usulu-molohiya-T9C33.png` |
| 8 | `✓ Bitti` | **AI-Worker-1** | **Kıbrıs Usulü Tavuklu Kolokas** | Kıbrıs Mutfağı | Ana Yemek | `recipe--kibris-usulu-tavuklu-kolokas-C0DS3.png` |
| 9 | `! Engelli` | - | **Kıbrıs Usulü Tavuklu Kolokas (Tarif başlığı tavuklu, canlı malzeme listesi kuzu eti içeriyor; yemek kimliği düzeltilmeden görsel teslim edilmez.)** | Kıbrıs Mutfağı | Ana Yemek | `recipe--kibris-usulu-tavuklu-kolokas-S8VGF.png` |
| 10 | `✓ Bitti` | **AI-Worker-1** | **Kıbrıs Usulü Şeftali Kebabı** | Kıbrıs Mutfağı | Ana Yemek | `recipe--kibris-usulu-seftali-kebabi-PK57V.png` |
| 11 | `✓ Bitti` | **Automation-Images-20260916** | **Kıbrıs Usulü Şeftali Kebabı** | Kıbrıs Mutfağı | Ana Yemek | `recipe--kibris-usulu-seftali-kebabi-KEFH3.png` |
| 12 | `! Engelli` | - | **Limonlu Balık Tepsisi (Canlı tarif status=draft; yayın koşulu sağlanmadığından görsel üretilmedi.)** | Kıbrıs Mutfağı | Ana Yemek | `recipe--limonlu-balik-tepsisi.png` |
| 13 | `! Engelli` | - | **Peynirli Kraker (Canlı tarifte peynir yok; malzemeler un/yumurta/süt/yoğurt ve adımlar jenerik tencere-tava. Peynirli kraker kimliği doğrulanamadı.)** | Kıbrıs Mutfağı | Atıştırmalık / Pratik | `recipe--peynirli-kraker.png` |
| 14 | `✓ Bitti` | **Automation-Images-20260916** | **Kıbrıs Hellimli Gözleme** | Kıbrıs Mutfağı | Hamur İşi | `recipe--kibris-hellimli-gozleme.png` |
| 15 | `[ ] Boşta` | - | **Kıbrıs Hellimli Gözleme** | Kıbrıs Mutfağı | Hamur İşi | `recipe--kibris-hellimli-gozleme-6QTYM.png` |
| 16 | `[ ] Boşta` | - | **Kıbrıs Kolokoti** | Kıbrıs Mutfağı | Hamur İşi | `recipe--kibris-kolokoti.png` |
| 17 | `[ ] Boşta` | - | **Kıbrıs Kolokoti** | Kıbrıs Mutfağı | Hamur İşi | `recipe--kibris-kolokoti-03VHY.png` |
| 18 | `[ ] Boşta` | - | **Kıbrıs Usulü Flaouna** | Kıbrıs Mutfağı | Hamur İşi | `recipe--kibris-usulu-flaouna.png` |
| 19 | `[ ] Boşta` | - | **Kıbrıs Usulü Flaouna** | Kıbrıs Mutfağı | Hamur İşi | `recipe--kibris-usulu-flaouna-4546F.png` |
| 20 | `[ ] Boşta` | - | **Soğanlı Çörek** | Kıbrıs Mutfağı | Hamur İşi | `recipe--soganli-corek.png` |
| 21 | `[ ] Boşta` | - | **Soğanlı Çörek** | Kıbrıs Mutfağı | Hamur İşi | `recipe--soganli-corek-Y92V8.png` |
| 22 | `[ ] Boşta` | - | **Izgara Hellimli Köy Ekmeği** | Kıbrıs Mutfağı | Kahvaltı | `recipe--izgara-hellimli-koy-ekmegi-5CAJQ.png` |
| 23 | `[ ] Boşta` | - | **Kıbrıs Usulü Pişi (Lokisi)** | Kıbrıs Mutfağı | Kahvaltı | `recipe--kibris-usulu-pisi-lokisi-AVVJ1.png` |
| 24 | `[ ] Boşta` | - | **Kıbrıs Usulü Pişi (Lokisi)** | Kıbrıs Mutfağı | Kahvaltı | `recipe--kibris-usulu-pisi-lokisi-HQ1AZ.png` |
| 25 | `[ ] Boşta` | - | **Geleneksel Tahinli** | Kıbrıs Mutfağı | Salata / Meze | `recipe--geleneksel-tahinli-V9X0W.png` |
| 26 | `[ ] Boşta` | - | **Kıbrıs Usulü** | Kıbrıs Mutfağı | Salata / Meze | `recipe--kibris-usulu-TEAG6.png` |
| 27 | `[ ] Boşta` | - | **Kıbrıs Macunu (Cevizli Sucuk)** | Kıbrıs Mutfağı | Tatlı | `recipe--kibris-macunu-cevizli-sucuk-PF01Y.png` |
| 28 | `[ ] Boşta` | - | **Kıbrıs Macunu (Cevizli Sucuk)** | Kıbrıs Mutfağı | Tatlı | `recipe--kibris-macunu-cevizli-sucuk-H98M0.png` |
| 29 | `[ ] Boşta` | - | **Kıbrıs Tatlısı (Şerbetli & Kremalı)** | Kıbrıs Mutfağı | Tatlı | `recipe--kibris-tatlisi-serbetli-kremali-5GT27.png` |
| 30 | `[ ] Boşta` | - | **Kıbrıs Tatlısı (Şerbetli & Kremalı)** | Kıbrıs Mutfağı | Tatlı | `recipe--kibris-tatlisi-serbetli-kremali-Y3WES.png` |
| 31 | `[ ] Boşta` | - | **Limonlu Otlu Patates** | Kıbrıs Mutfağı | Zeytinyağlı / Sebze | `recipe--limonlu-otlu-patates.png` |
| 32 | `[ ] Boşta` | - | **Mahluta Çorbası** | Kıbrıs Mutfağı | Çorba | `recipe--mahluta-corbasi-Q14JV.png` |
| 33 | `[ ] Boşta` | - | **Mahluta Çorbası** | Kıbrıs Mutfağı | Çorba | `recipe--mahluta-corbasi-JJEJ6.png` |
| 34 | `[ ] Boşta` | - | **Adana Kebabı** | Türk Mutfağı | Ana Yemek | `recipe--adana-kebabi-E8QBB.png` |
| 35 | `[ ] Boşta` | - | **Adana Kebabı** | Türk Mutfağı | Ana Yemek | `recipe--adana-kebabi-Y5MTK.png` |
| 36 | `[ ] Boşta` | - | **Ali Nazik Kebabı** | Türk Mutfağı | Ana Yemek | `recipe--ali-nazik-kebabi-6EG3R.png` |
| 37 | `[ ] Boşta` | - | **Ali Nazik Kebabı** | Türk Mutfağı | Ana Yemek | `recipe--ali-nazik-kebabi-981YE.png` |
| 38 | `[ ] Boşta` | - | **Asma Yaprağında Sardalya** | Türk Mutfağı | Ana Yemek | `recipe--asma-yapraginda-sardalya-00BMX.png` |
| 39 | `[ ] Boşta` | - | **Avcı Kebabı** | Türk Mutfağı | Ana Yemek | `recipe--avci-kebabi-HB849.png` |
| 40 | `[ ] Boşta` | - | **Avcı Kebabı** | Türk Mutfağı | Ana Yemek | `recipe--avci-kebabi-JE0JW.png` |
| 41 | `[ ] Boşta` | - | **Baharatlı Tavuk** | Türk Mutfağı | Ana Yemek | `recipe--baharatli-tavuk-XBSTW.png` |
| 42 | `[ ] Boşta` | - | **Beyti** | Türk Mutfağı | Ana Yemek | `recipe--beyti-6C04R.png` |
| 43 | `[ ] Boşta` | - | **Beyti** | Türk Mutfağı | Ana Yemek | `recipe--beyti-30BTS.png` |
| 44 | `[ ] Boşta` | - | **Bolu Usulü Orman Kebabı** | Türk Mutfağı | Ana Yemek | `recipe--bolu-usulu-orman-kebabi-VN45V.png` |
| 45 | `[ ] Boşta` | - | **Bolu Usulü Orman Kebabı** | Türk Mutfağı | Ana Yemek | `recipe--bolu-usulu-orman-kebabi-XM3GW.png` |
| 46 | `[ ] Boşta` | - | **Domatesli Tavuk Sote** | Türk Mutfağı | Ana Yemek | `recipe--domatesli-tavuk-sote.png` |
| 47 | `[ ] Boşta` | - | **Fırın Tavuk** | Türk Mutfağı | Ana Yemek | `recipe--firin-tavuk-KHD06.png` |
| 48 | `[ ] Boşta` | - | **Fırında Kuzu Tandır** | Türk Mutfağı | Ana Yemek | `recipe--firinda-kuzu-tandir-NPPHP.png` |
| 49 | `[ ] Boşta` | - | **Fırında Kuzu Tandır** | Türk Mutfağı | Ana Yemek | `recipe--firinda-kuzu-tandir-HN514.png` |
| 50 | `[ ] Boşta` | - | **Fırında Kuzu İncik** | Türk Mutfağı | Ana Yemek | `recipe--firinda-kuzu-incik-ZER6G.png` |
| 51 | `[ ] Boşta` | - | **Fırında Kuzu İncik** | Türk Mutfağı | Ana Yemek | `recipe--firinda-kuzu-incik-SN24G.png` |
| 52 | `[ ] Boşta` | - | **Fırında Kuzu İncik** | Türk Mutfağı | Ana Yemek | `recipe--firinda-kuzu-incik-VPCYM.png` |
| 53 | `[ ] Boşta` | - | **Fırında Kuzu İncik** | Türk Mutfağı | Ana Yemek | `recipe--firinda-kuzu-incik-0C4YC.png` |
| 54 | `[ ] Boşta` | - | **Fırında Köfte Patates** | Türk Mutfağı | Ana Yemek | `recipe--firinda-kofte-patates-1BXF7.png` |
| 55 | `[ ] Boşta` | - | **Fırında Köfte Patates** | Türk Mutfağı | Ana Yemek | `recipe--firinda-kofte-patates-1C373.png` |
| 56 | `[ ] Boşta` | - | **Fırında Tavuk** | Türk Mutfağı | Ana Yemek | `recipe--firinda-tavuk-QMJXN.png` |
| 57 | `[ ] Boşta` | - | **Geleneksel Etli Nohut Yemeği** | Türk Mutfağı | Ana Yemek | `recipe--geleneksel-etli-nohut-yemegi-GRNH2.png` |
| 58 | `[ ] Boşta` | - | **Geleneksel Etli Nohut Yemeği** | Türk Mutfağı | Ana Yemek | `recipe--geleneksel-etli-nohut-yemegi-VYFF7.png` |
| 59 | `[ ] Boşta` | - | **Geleneksel Sac Kavurma** | Türk Mutfağı | Ana Yemek | `recipe--geleneksel-sac-kavurma-DJ5FC.png` |
| 60 | `[ ] Boşta` | - | **Geleneksel Sac Kavurma** | Türk Mutfağı | Ana Yemek | `recipe--geleneksel-sac-kavurma-B52WV.png` |
| 61 | `[ ] Boşta` | - | **Hamsi** | Türk Mutfağı | Ana Yemek | `recipe--hamsi-J07ZW.png` |
| 62 | `[ ] Boşta` | - | **Hünkar Beğendi** | Türk Mutfağı | Ana Yemek | `recipe--hunkar-begendi-T580D.png` |
| 63 | `[ ] Boşta` | - | **Hünkar Beğendi** | Türk Mutfağı | Ana Yemek | `recipe--hunkar-begendi-XTC43.png` |
| 64 | `[ ] Boşta` | - | **Karides Güveç** | Türk Mutfağı | Ana Yemek | `recipe--karides-guvec-SG3HE.png` |
| 65 | `[ ] Boşta` | - | **Karides Güveç** | Türk Mutfağı | Ana Yemek | `recipe--karides-guvec-J8KDV.png` |
| 66 | `[ ] Boşta` | - | **Klasik Etli Kuru Fasulye** | Türk Mutfağı | Ana Yemek | `recipe--klasik-etli-kuru-fasulye-CTY1M.png` |
| 67 | `[ ] Boşta` | - | **Klasik Etli Kuru Fasulye** | Türk Mutfağı | Ana Yemek | `recipe--klasik-etli-kuru-fasulye-62P38.png` |
| 68 | `[ ] Boşta` | - | **Kremalı Mantarlı** | Türk Mutfağı | Ana Yemek | `recipe--kremali-mantarli-121GG.png` |
| 69 | `[ ] Boşta` | - | **Kremalı Mantarlı** | Türk Mutfağı | Ana Yemek | `recipe--kremali-mantarli-9DG00.png` |
| 70 | `[ ] Boşta` | - | **Kıymalı Kapuska Yemeği** | Türk Mutfağı | Ana Yemek | `recipe--kiymali-kapuska-yemegi-RB7HT.png` |
| 71 | `[ ] Boşta` | - | **Kıymalı Kapuska Yemeği** | Türk Mutfağı | Ana Yemek | `recipe--kiymali-kapuska-yemegi-Q5SEJ.png` |
| 72 | `[ ] Boşta` | - | **Lokum Et** | Türk Mutfağı | Ana Yemek | `recipe--lokum-et-AFP4R.png` |
| 73 | `[ ] Boşta` | - | **Mantarlı Risotto** | İtalyan Mutfağı | Ana Yemek | `recipe--mantarli-risotto.png` |
| 74 | `[ ] Boşta` | - | **Mersin Usulü Tavuk Tantuni** | Türk Mutfağı | Ana Yemek | `recipe--mersin-usulu-tavuk-tantuni-MMEAV.png` |
| 75 | `[ ] Boşta` | - | **Mersin Usulü Tavuk Tantuni** | Türk Mutfağı | Ana Yemek | `recipe--mersin-usulu-tavuk-tantuni-3G5TV.png` |
| 76 | `[ ] Boşta` | - | **Meyhane Usulü Etli Bulgur Pilavı** | Türk Mutfağı | Ana Yemek | `recipe--meyhane-usulu-etli-bulgur-pilavi-7A0N9.png` |
| 77 | `[ ] Boşta` | - | **Meyhane Usulü Etli Bulgur Pilavı** | Türk Mutfağı | Ana Yemek | `recipe--meyhane-usulu-etli-bulgur-pilavi-AZ89T.png` |
| 78 | `[ ] Boşta` | - | **Mısır Unlu Karadeniz Hamsi** | Türk Mutfağı | Ana Yemek | `recipe--misir-unlu-karadeniz-hamsi-NY4TZ.png` |
| 79 | `[ ] Boşta` | - | **Pideli Köfte** | Türk Mutfağı | Ana Yemek | `recipe--pideli-kofte-TAJEE.png` |
| 80 | `[ ] Boşta` | - | **Pideli Köfte** | Türk Mutfağı | Ana Yemek | `recipe--pideli-kofte-61Q59.png` |
| 81 | `[ ] Boşta` | - | **Sardalya** | Türk Mutfağı | Ana Yemek | `recipe--sardalya-0NAD4.png` |
| 82 | `[ ] Boşta` | - | **Sarımsaklı Zeytinyağlı Makarna** | İtalyan Mutfağı | Ana Yemek | `recipe--sarimsakli-zeytinyagli-makarna.png` |
| 83 | `[ ] Boşta` | - | **Sebzeli Tavuk** | Türk Mutfağı | Ana Yemek | `recipe--sebzeli-tavuk-6TPEV.png` |
| 84 | `[ ] Boşta` | - | **Tas Kebabı** | Türk Mutfağı | Ana Yemek | `recipe--tas-kebabi-4AQQM.png` |
| 85 | `[ ] Boşta` | - | **Tas Kebabı** | Türk Mutfağı | Ana Yemek | `recipe--tas-kebabi-S5CFD.png` |
| 86 | `[ ] Boşta` | - | **Tavada Levrek Buğulama** | Türk Mutfağı | Ana Yemek | `recipe--tavada-levrek-bugulama-JM4ZZ.png` |
| 87 | `[ ] Boşta` | - | **Tavada Levrek Buğulama** | Türk Mutfağı | Ana Yemek | `recipe--tavada-levrek-bugulama-Y9734.png` |
| 88 | `[ ] Boşta` | - | **Çöp Şiş** | Türk Mutfağı | Ana Yemek | `recipe--cop-sis-HW6SF.png` |
| 89 | `[ ] Boşta` | - | **Çöp Şiş** | Türk Mutfağı | Ana Yemek | `recipe--cop-sis-QAATK.png` |
| 90 | `[ ] Boşta` | - | **İskender Kebabı** | Türk Mutfağı | Ana Yemek | `recipe--iskender-kebabi-Z406J.png` |
| 91 | `[ ] Boşta` | - | **İskender Kebabı** | Türk Mutfağı | Ana Yemek | `recipe--iskender-kebabi-0N6PA.png` |
| 92 | `[ ] Boşta` | - | **İzmir Köfte** | Türk Mutfağı | Ana Yemek | `recipe--izmir-kofte-9EYTX.png` |
| 93 | `[ ] Boşta` | - | **İzmir Köfte** | Türk Mutfağı | Ana Yemek | `recipe--izmir-kofte-2DP3F.png` |
| 94 | `[ ] Boşta` | - | **Baharatlı Çıtır Patates Kızartması** | Türk Mutfağı | Atıştırmalık / Pratik | `recipe--baharatli-citir-patates-kizartmasi-9A67W.png` |
| 95 | `[ ] Boşta` | - | **Baharatlı Çıtır Patates Kızartması** | Türk Mutfağı | Atıştırmalık / Pratik | `recipe--baharatli-citir-patates-kizartmasi-H4K4K.png` |
| 96 | `[ ] Boşta` | - | **Bol Malzemeli Ev Kumpiri** | Türk Mutfağı | Atıştırmalık / Pratik | `recipe--bol-malzemeli-ev-kumpiri-VCCFQ.png` |
| 97 | `[ ] Boşta` | - | **Bol Malzemeli Ev Kumpiri** | Türk Mutfağı | Atıştırmalık / Pratik | `recipe--bol-malzemeli-ev-kumpiri-ZBA17.png` |
| 98 | `[ ] Boşta` | - | **Fırın Nohut** | Akdeniz Mutfağı | Atıştırmalık / Pratik | `recipe--firin-nohut.png` |
| 99 | `[ ] Boşta` | - | **Fırında İzmir Usulü Kokoreç** | Türk Mutfağı | Atıştırmalık / Pratik | `recipe--firinda-izmir-usulu-kokorec-MHAHB.png` |
| 100 | `[ ] Boşta` | - | **Fırında İzmir Usulü Kokoreç** | Türk Mutfağı | Atıştırmalık / Pratik | `recipe--firinda-izmir-usulu-kokorec-FKGTH.png` |
| 101 | `[ ] Boşta` | - | **Lahmacun Dürüm** | Türk Mutfağı | Atıştırmalık / Pratik | `recipe--lahmacun-durum-8FQ8V.png` |
| 102 | `[ ] Boşta` | - | **Lahmacun Dürüm** | Türk Mutfağı | Atıştırmalık / Pratik | `recipe--lahmacun-durum-JBFPK.png` |
| 103 | `[ ] Boşta` | - | **Mercimek Köftesi** | Türk Mutfağı | Atıştırmalık / Pratik | `recipe--mercimek-koftesi.png` |
| 104 | `[ ] Boşta` | - | **Midye** | Türk Mutfağı | Atıştırmalık / Pratik | `recipe--midye-EG6Y4.png` |
| 105 | `[ ] Boşta` | - | **Midye** | Türk Mutfağı | Atıştırmalık / Pratik | `recipe--midye-P0SZZ.png` |
| 106 | `[ ] Boşta` | - | **Tantuni Dürüm** | Türk Mutfağı | Atıştırmalık / Pratik | `recipe--tantuni-durum-2NPXJ.png` |
| 107 | `[ ] Boşta` | - | **Tantuni Dürüm** | Türk Mutfağı | Atıştırmalık / Pratik | `recipe--tantuni-durum-82S8X.png` |
| 108 | `[ ] Boşta` | - | **Zaatarlı Pide Dilimleri** | Orta Doğu Mutfağı | Atıştırmalık / Pratik | `recipe--zaatarli-pide-dilimleri.png` |
| 109 | `[ ] Boşta` | - | **Çiğ Köfte** | Türk Mutfağı | Atıştırmalık / Pratik | `recipe--cig-kofte-83VT7.png` |
| 110 | `[ ] Boşta` | - | **Çiğ Köfte** | Türk Mutfağı | Atıştırmalık / Pratik | `recipe--cig-kofte-YWCS4.png` |
| 111 | `[ ] Boşta` | - | **T** | X | Genel | `recipe--t-6a97da3852b4d.png` |
| 112 | `[ ] Boşta` | - | **T6a97da6dae55f** | Türk Mutfağı | Genel | `recipe--t-6a97da6dae560.png` |
| 113 | `[ ] Boşta` | - | **Akdeniz Yassı** | Akdeniz Mutfağı | Hamur İşi | `recipe--akdeniz-yassi-HJEXY.png` |
| 114 | `[ ] Boşta` | - | **Akdeniz Yassı** | Akdeniz Mutfağı | Hamur İşi | `recipe--akdeniz-yassi-970NE.png` |
| 115 | `[ ] Boşta` | - | **Akdeniz Zeytinli** | Akdeniz Mutfağı | Hamur İşi | `recipe--akdeniz-zeytinli-BWXHM.png` |
| 116 | `[ ] Boşta` | - | **Akdeniz Zeytinli** | Akdeniz Mutfağı | Hamur İşi | `recipe--akdeniz-zeytinli-G0EZ6.png` |
| 117 | `[ ] Boşta` | - | **Cevizli Baklava** | Türk Mutfağı | Hamur İşi | `recipe--cevizli-baklava.png` |
| 118 | `[ ] Boşta` | - | **Cevizli Baklava** | Türk Mutfağı | Hamur İşi | `recipe--cevizli-baklava-J7PBK.png` |
| 119 | `[ ] Boşta` | - | **Cevizli Sarma Çörek** | Akdeniz Mutfağı | Hamur İşi | `recipe--cevizli-sarma-corek-HD9YE.png` |
| 120 | `[ ] Boşta` | - | **Dilber Dudağı** | Türk Mutfağı | Hamur İşi | `recipe--dilber-dudagi.png` |
| 121 | `[ ] Boşta` | - | **Dilber Dudağı** | Türk Mutfağı | Hamur İşi | `recipe--dilber-dudagi-9WVNG.png` |
| 122 | `[ ] Boşta` | - | **El Açması Peynirli Gözleme** | Türk Mutfağı | Hamur İşi | `recipe--el-acmasi-peynirli-gozleme-ESGMD.png` |
| 123 | `[ ] Boşta` | - | **El Açması Peynirli Gözleme** | Türk Mutfağı | Hamur İşi | `recipe--el-acmasi-peynirli-gozleme-544JX.png` |
| 124 | `[ ] Boşta` | - | **Geleneksel Çıtır Lahmacun** | Türk Mutfağı | Hamur İşi | `recipe--geleneksel-citir-lahmacun-YJV2W.png` |
| 125 | `[ ] Boşta` | - | **Geleneksel Çıtır Lahmacun** | Türk Mutfağı | Hamur İşi | `recipe--geleneksel-citir-lahmacun-885J7.png` |
| 126 | `[ ] Boşta` | - | **Ispanaklı Börek** | Türk Mutfağı | Hamur İşi | `recipe--ispanakli-borek.png` |
| 127 | `[ ] Boşta` | - | **Ispanaklı Börek** | Türk Mutfağı | Hamur İşi | `recipe--ispanakli-borek-4XEX5.png` |
| 128 | `[ ] Boşta` | - | **Katmer** | Türk Mutfağı | Hamur İşi | `recipe--katmer.png` |
| 129 | `[ ] Boşta` | - | **Katmer** | Türk Mutfağı | Hamur İşi | `recipe--katmer-W39JQ.png` |
| 130 | `[ ] Boşta` | - | **Kayseri Mantısı** | Türk Mutfağı | Hamur İşi | `recipe--kayseri-mantisi-S898Y.png` |
| 131 | `[ ] Boşta` | - | **Kayseri Mantısı** | Türk Mutfağı | Hamur İşi | `recipe--kayseri-mantisi-0QDQA.png` |
| 132 | `[ ] Boşta` | - | **Konya Etli** | Türk Mutfağı | Hamur İşi | `recipe--konya-etli-H2403.png` |
| 133 | `[ ] Boşta` | - | **Köy Usulü Sac Böreği** | Türk Mutfağı | Hamur İşi | `recipe--koy-usulu-sac-boregi-WF2GC.png` |
| 134 | `[ ] Boşta` | - | **Köy Usulü Sac Böreği** | Türk Mutfağı | Hamur İşi | `recipe--koy-usulu-sac-boregi-BK80X.png` |
| 135 | `[ ] Boşta` | - | **Kıymalı Kol Böreği** | Türk Mutfağı | Hamur İşi | `recipe--kiymali-kol-boregi-XPEK0.png` |
| 136 | `[ ] Boşta` | - | **Kıymalı Kol Böreği** | Türk Mutfağı | Hamur İşi | `recipe--kiymali-kol-boregi-KSHEV.png` |
| 137 | `[ ] Boşta` | - | **Kıymalı ve Peynirli** | Türk Mutfağı | Hamur İşi | `recipe--kiymali-ve-peynirli-2HQXF.png` |
| 138 | `[ ] Boşta` | - | **Kıymalı ve Peynirli** | Türk Mutfağı | Hamur İşi | `recipe--kiymali-ve-peynirli-PJBED.png` |
| 139 | `[ ] Boşta` | - | **Lor Peynirli Tepsi Böreği** | Türk Mutfağı | Hamur İşi | `recipe--lor-peynirli-tepsi-boregi.png` |
| 140 | `[ ] Boşta` | - | **Lor Peynirli Tepsi Böreği** | Türk Mutfağı | Hamur İşi | `recipe--lor-peynirli-tepsi-boregi-K2CJ7.png` |
| 141 | `[ ] Boşta` | - | **Peynirli Mayasız Poğaça** | Türk Mutfağı | Hamur İşi | `recipe--peynirli-mayasiz-pogaca-56QC1.png` |
| 142 | `[ ] Boşta` | - | **Peynirli Mayasız Poğaça** | Türk Mutfağı | Hamur İşi | `recipe--peynirli-mayasiz-pogaca-57FE3.png` |
| 143 | `[ ] Boşta` | - | **Pratik Lavaş Ekmeği** | Akdeniz Mutfağı | Hamur İşi | `recipe--pratik-lavas-ekmegi-R91JQ.png` |
| 144 | `[ ] Boşta` | - | **Pratik Lavaş Ekmeği** | Akdeniz Mutfağı | Hamur İşi | `recipe--pratik-lavas-ekmegi.png` |
| 145 | `[ ] Boşta` | - | **Pratik Lavaş Ekmeği** | Akdeniz Mutfağı | Hamur İşi | `recipe--pratik-lavas-ekmegi-M27X2.png` |
| 146 | `[ ] Boşta` | - | **Sebzeli Kiş** | Akdeniz Mutfağı | Hamur İşi | `recipe--sebzeli-kis.png` |
| 147 | `[ ] Boşta` | - | **Sokak Simidi** | Türk Mutfağı | Hamur İşi | `recipe--sokak-simidi-MVKPW.png` |
| 148 | `[ ] Boşta` | - | **Sokak Simidi** | Türk Mutfağı | Hamur İşi | `recipe--sokak-simidi-YSK30.png` |
| 149 | `[ ] Boşta` | - | **Su Böreği** | Türk Mutfağı | Hamur İşi | `recipe--su-boregi.png` |
| 150 | `[ ] Boşta` | - | **Su Böreği** | Türk Mutfağı | Hamur İşi | `recipe--su-boregi-RZZB0.png` |
| 151 | `[ ] Boşta` | - | **Sütlü Revani** | Türk Mutfağı | Hamur İşi | `recipe--sutlu-revani.png` |
| 152 | `[ ] Boşta` | - | **Sütlü Revani** | Türk Mutfağı | Hamur İşi | `recipe--sutlu-revani-6Y2J1.png` |
| 153 | `[ ] Boşta` | - | **Tatlı Tarçınlı** | Akdeniz Mutfağı | Hamur İşi | `recipe--tatli-tarcinli-NJXBH.png` |
| 154 | `[ ] Boşta` | - | **Tatlı Tarçınlı** | Akdeniz Mutfağı | Hamur İşi | `recipe--tatli-tarcinli-BAGX9.png` |
| 155 | `[ ] Boşta` | - | **Tavada Köy Bazlaması** | Türk Mutfağı | Hamur İşi | `recipe--tavada-koy-bazlamasi-M96PJ.png` |
| 156 | `[ ] Boşta` | - | **Tavada Köy Bazlaması** | Türk Mutfağı | Hamur İşi | `recipe--tavada-koy-bazlamasi-HMJ6Q.png` |
| 157 | `[ ] Boşta` | - | **Yoğurtlu Poğaça** | Türk Mutfağı | Hamur İşi | `recipe--yogurtlu-pogaca.png` |
| 158 | `[ ] Boşta` | - | **Yoğurtlu Poğaça** | Türk Mutfağı | Hamur İşi | `recipe--yogurtlu-pogaca-8X9SF.png` |
| 159 | `[ ] Boşta` | - | **Yoğurtlu Soğan Halkası** | Akdeniz Mutfağı | Hamur İşi | `recipe--yogurtlu-sogan-halkasi.png` |
| 160 | `[ ] Boşta` | - | **Yoğurtlu Soğan Halkası** | Akdeniz Mutfağı | Hamur İşi | `recipe--yogurtlu-sogan-halkasi-8QPPA.png` |
| 161 | `[ ] Boşta` | - | **Yumuşacık Ev Açması** | Türk Mutfağı | Hamur İşi | `recipe--yumusacik-ev-acmasi-W1BJ6.png` |
| 162 | `[ ] Boşta` | - | **Yumuşacık Ev Açması** | Türk Mutfağı | Hamur İşi | `recipe--yumusacik-ev-acmasi-7HF11.png` |
| 163 | `[ ] Boşta` | - | **Zeytinli Açma** | Türk Mutfağı | Hamur İşi | `recipe--zeytinli-acma.png` |
| 164 | `[ ] Boşta` | - | **Çiğ Börek** | Türk Mutfağı | Hamur İşi | `recipe--cig-borek-723AZ.png` |
| 165 | `[ ] Boşta` | - | **Çiğ Börek** | Türk Mutfağı | Hamur İşi | `recipe--cig-borek-S0X26.png` |
| 166 | `[ ] Boşta` | - | **İçli Köfte** | Türk Mutfağı | Hamur İşi | `recipe--icli-kofte-JT2QY.png` |
| 167 | `[ ] Boşta` | - | **İçli Köfte** | Türk Mutfağı | Hamur İşi | `recipe--icli-kofte-608KM.png` |
| 168 | `[ ] Boşta` | - | **Bakır Tavada Sucuklu Yumurta** | Türk Mutfağı | Kahvaltı | `recipe--bakir-tavada-sucuklu-yumurta-90TWW.png` |
| 169 | `[ ] Boşta` | - | **Bakır Tavada Sucuklu Yumurta** | Türk Mutfağı | Kahvaltı | `recipe--bakir-tavada-sucuklu-yumurta-0SHGD.png` |
| 170 | `[ ] Boşta` | - | **El Açması Peynirli Gözleme** | Türk Mutfağı | Kahvaltı | `recipe--el-acmasi-peynirli-gozleme-RJVH2.png` |
| 171 | `[ ] Boşta` | - | **El Açması Peynirli Gözleme** | Türk Mutfağı | Kahvaltı | `recipe--el-acmasi-peynirli-gozleme-CMSQR.png` |
| 172 | `[ ] Boşta` | - | **Gaziantep Katmeri** | Türk Mutfağı | Kahvaltı | `recipe--gaziantep-katmeri-GC3DH.png` |
| 173 | `[ ] Boşta` | - | **Gaziantep Katmeri** | Türk Mutfağı | Kahvaltı | `recipe--gaziantep-katmeri-D5FF6.png` |
| 174 | `[ ] Boşta` | - | **Hakiki Bal Kaymak Tabağı** | Türk Mutfağı | Kahvaltı | `recipe--hakiki-bal-kaymak-tabagi-0ADCJ.png` |
| 175 | `[ ] Boşta` | - | **Hakiki Bal Kaymak Tabağı** | Türk Mutfağı | Kahvaltı | `recipe--hakiki-bal-kaymak-tabagi-JE72H.png` |
| 176 | `[ ] Boşta` | - | **Peynirli Omlet** | Yunan Mutfağı | Kahvaltı | `recipe--peynirli-omlet.png` |
| 177 | `[ ] Boşta` | - | **Sarımsaklı Yoğurtlu Çılbır** | Türk Mutfağı | Kahvaltı | `recipe--sarimsakli-yogurtlu-cilbir-HXK50.png` |
| 178 | `[ ] Boşta` | - | **Sarımsaklı Yoğurtlu Çılbır** | Türk Mutfağı | Kahvaltı | `recipe--sarimsakli-yogurtlu-cilbir-WH7WY.png` |
| 179 | `[ ] Boşta` | - | **Yoğurtlu Yulaf Kasesi** | Akdeniz Mutfağı | Kahvaltı | `recipe--yogurtlu-yulaf-kasesi.png` |
| 180 | `[ ] Boşta` | - | **Çıtır Sigara Böreği** | Türk Mutfağı | Kahvaltı | `recipe--citir-sigara-boregi-9GBCW.png` |
| 181 | `[ ] Boşta` | - | **Çıtır Sigara Böreği** | Türk Mutfağı | Kahvaltı | `recipe--citir-sigara-boregi-R9QRZ.png` |
| 182 | `[ ] Boşta` | - | **Acılı Babaganuş** | Türk Mutfağı | Salata / Meze | `recipe--acili-babaganus-6ZQWX.png` |
| 183 | `[ ] Boşta` | - | **Acılı Babaganuş** | Türk Mutfağı | Salata / Meze | `recipe--acili-babaganus-Z3D9S.png` |
| 184 | `[ ] Boşta` | - | **Antakya Usulü Kısır** | Türk Mutfağı | Salata / Meze | `recipe--antakya-usulu-kisir-P773C.png` |
| 185 | `[ ] Boşta` | - | **Antakya Usulü Kısır** | Türk Mutfağı | Salata / Meze | `recipe--antakya-usulu-kisir-9852K.png` |
| 186 | `[ ] Boşta` | - | **Antalya Usulü Tahinli** | Türk Mutfağı | Salata / Meze | `recipe--antalya-usulu-tahinli-8T62D.png` |
| 187 | `[ ] Boşta` | - | **Antalya Usulü Tahinli Piyaz** | Türk Mutfağı | Salata / Meze | `recipe--antalya-usulu-tahinli-piyaz-QS9PM.png` |
| 188 | `[ ] Boşta` | - | **Atom Acılı Ezme Mezesi** | Türk Mutfağı | Salata / Meze | `recipe--atom-acili-ezme-mezesi-ZY3X2.png` |
| 189 | `[ ] Boşta` | - | **Atom Acılı Ezme Mezesi** | Türk Mutfağı | Salata / Meze | `recipe--atom-acili-ezme-mezesi-3FWG2.png` |
| 190 | `[ ] Boşta` | - | **Atom Acılı Ezme Mezesi** | Türk Mutfağı | Salata / Meze | `recipe--atom-acili-ezme-mezesi-66WRB.png` |
| 191 | `[ ] Boşta` | - | **Atom Acılı Ezme Mezesi** | Türk Mutfağı | Salata / Meze | `recipe--atom-acili-ezme-mezesi-27M9W.png` |
| 192 | `[ ] Boşta` | - | **Babaganuş (Köz Patlıcan Salatası)** | Türk Mutfağı | Salata / Meze | `recipe--babaganus-koz-patlican-salatasi-AEYF1.png` |
| 193 | `[ ] Boşta` | - | **Babaganuş (Köz Patlıcan Salatası)** | Türk Mutfağı | Salata / Meze | `recipe--babaganus-koz-patlican-salatasi-5FFBY.png` |
| 194 | `[ ] Boşta` | - | **Cevizli Gavurdağı Salatası** | Türk Mutfağı | Salata / Meze | `recipe--cevizli-gavurdagi-salatasi-GYZC8.png` |
| 195 | `[ ] Boşta` | - | **Cevizli Gavurdağı Salatası** | Türk Mutfağı | Salata / Meze | `recipe--cevizli-gavurdagi-salatasi-FDT3Y.png` |
| 196 | `[ ] Boşta` | - | **Cevizli Muhammara (Acuka)** | Türk Mutfağı | Salata / Meze | `recipe--cevizli-muhammara-acuka-6GA31.png` |
| 197 | `[ ] Boşta` | - | **Cevizli Muhammara (Acuka)** | Türk Mutfağı | Salata / Meze | `recipe--cevizli-muhammara-acuka-R8DAG.png` |
| 198 | `[ ] Boşta` | - | **Cevizli Muhammara (Acuka)** | Türk Mutfağı | Salata / Meze | `recipe--cevizli-muhammara-acuka-1EAK2.png` |
| 199 | `[ ] Boşta` | - | **Cevizli Muhammara (Acuka)** | Türk Mutfağı | Salata / Meze | `recipe--cevizli-muhammara-acuka-1AP8T.png` |
| 200 | `[ ] Boşta` | - | **Cevizli Tarator Sos** | Türk Mutfağı | Salata / Meze | `recipe--cevizli-tarator-sos-95AD3.png` |
| 201 | `[ ] Boşta` | - | **Cevizli Tarator Sos** | Türk Mutfağı | Salata / Meze | `recipe--cevizli-tarator-sos-17766.png` |
| 202 | `[ ] Boşta` | - | **Dereotlu Kuru Bakla Favasi** | Türk Mutfağı | Salata / Meze | `recipe--dereotlu-kuru-bakla-favasi-VTM0R.png` |
| 203 | `[ ] Boşta` | - | **Dereotlu Kuru Bakla Favasi** | Türk Mutfağı | Salata / Meze | `recipe--dereotlu-kuru-bakla-favasi-XHTMT.png` |
| 204 | `[ ] Boşta` | - | **Ermeni Mezesi** | Türk Mutfağı | Salata / Meze | `recipe--ermeni-mezesi-DTSBQ.png` |
| 205 | `[ ] Boşta` | - | **Ermeni Mezesi Topik** | Türk Mutfağı | Salata / Meze | `recipe--ermeni-mezesi-topik-N7QGV.png` |
| 206 | `[ ] Boşta` | - | **Ev Yapımı Rus Salatası** | Türk Mutfağı | Salata / Meze | `recipe--ev-yapimi-rus-salatasi-ZFSFP.png` |
| 207 | `[ ] Boşta` | - | **Ev Yapımı Rus Salatası** | Türk Mutfağı | Salata / Meze | `recipe--ev-yapimi-rus-salatasi-Z1XNW.png` |
| 208 | `[ ] Boşta` | - | **Geleneksel Çoban Salatası** | Türk Mutfağı | Salata / Meze | `recipe--geleneksel-coban-salatasi-TY4RT.png` |
| 209 | `[ ] Boşta` | - | **Geleneksel Çoban Salatası** | Türk Mutfağı | Salata / Meze | `recipe--geleneksel-coban-salatasi-FC784.png` |
| 210 | `[ ] Boşta` | - | **Karışık Meze Tabağı** | Türk Mutfağı | Salata / Meze | `recipe--karisik-meze-tabagi-T21EA.png` |
| 211 | `[ ] Boşta` | - | **Karışık Meze Tabağı** | Türk Mutfağı | Salata / Meze | `recipe--karisik-meze-tabagi-2HKVQ.png` |
| 212 | `[ ] Boşta` | - | **Patlıcan Közlemesi** | Türk Mutfağı | Salata / Meze | `recipe--patlican-kozlemesi-AMD2T.png` |
| 213 | `[ ] Boşta` | - | **Patlıcan Közlemesi** | Türk Mutfağı | Salata / Meze | `recipe--patlican-kozlemesi-CDW76.png` |
| 214 | `[ ] Boşta` | - | **Patlıcan Közlemesi** | Türk Mutfağı | Salata / Meze | `recipe--patlican-kozlemesi-S5060.png` |
| 215 | `[ ] Boşta` | - | **Sarımsaklı Yoğurtlu Haydari** | Türk Mutfağı | Salata / Meze | `recipe--sarimsakli-yogurtlu-haydari-JWNB7.png` |
| 216 | `[ ] Boşta` | - | **Sarımsaklı Yoğurtlu Haydari** | Türk Mutfağı | Salata / Meze | `recipe--sarimsakli-yogurtlu-haydari-92KEM.png` |
| 217 | `[ ] Boşta` | - | **Tabule** | Lübnan Mutfağı | Salata / Meze | `recipe--tabule.png` |
| 218 | `[ ] Boşta` | - | **Tahinli Havuç Salatası** | Türk Mutfağı | Salata / Meze | `recipe--tahinli-havuc-salatasi-YTDG0.png` |
| 219 | `[ ] Boşta` | - | **Tahinli Havuç Salatası** | Türk Mutfağı | Salata / Meze | `recipe--tahinli-havuc-salatasi-RNY5X.png` |
| 220 | `[ ] Boşta` | - | **Yeşil Mercimek Salatası** | Türk Mutfağı | Salata / Meze | `recipe--yesil-mercimek-salatasi-PG4NP.png` |
| 221 | `[ ] Boşta` | - | **Yeşil Mercimek Salatası** | Türk Mutfağı | Salata / Meze | `recipe--yesil-mercimek-salatasi-B74D8.png` |
| 222 | `[ ] Boşta` | - | **Yeşil Mercimek Salatası** | Türk Mutfağı | Salata / Meze | `recipe--yesil-mercimek-salatasi-K8ZGX.png` |
| 223 | `[ ] Boşta` | - | **Yoğurtlu Nohut Salatası** | Lübnan Mutfağı | Salata / Meze | `recipe--yogurtlu-nohut-salatasi.png` |
| 224 | `[ ] Boşta` | - | **Yoğurtlu Semizotu Salatası** | Türk Mutfağı | Salata / Meze | `recipe--yogurtlu-semizotu-salatasi-4E9QT.png` |
| 225 | `[ ] Boşta` | - | **Yoğurtlu Semizotu Salatası** | Türk Mutfağı | Salata / Meze | `recipe--yogurtlu-semizotu-salatasi-29PKC.png` |
| 226 | `[ ] Boşta` | - | **Yoğurtlu Semizotu Salatası** | Türk Mutfağı | Salata / Meze | `recipe--yogurtlu-semizotu-salatasi-CTT0H.png` |
| 227 | `[ ] Boşta` | - | **Yoğurtlu Semizotu Salatası** | Türk Mutfağı | Salata / Meze | `recipe--yogurtlu-semizotu-salatasi-R31M5.png` |
| 228 | `[ ] Boşta` | - | **Yunan Usulü Fasulye Salatası** | Yunan Mutfağı | Salata / Meze | `recipe--yunan-usulu-fasulye-salatasi.png` |
| 229 | `[ ] Boşta` | - | **Zeytinyağlı ve Naneli Süzme** | Türk Mutfağı | Salata / Meze | `recipe--zeytinyagli-ve-naneli-suzme-NX0SP.png` |
| 230 | `[ ] Boşta` | - | **Zeytinyağlı ve Naneli Süzme Labne** | Türk Mutfağı | Salata / Meze | `recipe--zeytinyagli-ve-naneli-suzme-labne-7VMJK.png` |
| 231 | `[ ] Boşta` | - | **Cevizli Tel Kadayıf** | Türk Mutfağı | Tatlı | `recipe--cevizli-tel-kadayif-F7WS4.png` |
| 232 | `[ ] Boşta` | - | **Cevizli Tel Kadayıf** | Türk Mutfağı | Tatlı | `recipe--cevizli-tel-kadayif-4FVR7.png` |
| 233 | `[ ] Boşta` | - | **Damla Sakızlı Muhallebi** | Türk Mutfağı | Tatlı | `recipe--damla-sakizli-muhallebi-0VD83.png` |
| 234 | `[ ] Boşta` | - | **Damla Sakızlı Muhallebi** | Türk Mutfağı | Tatlı | `recipe--damla-sakizli-muhallebi-B04SZ.png` |
| 235 | `[ ] Boşta` | - | **Ev Yapımı Cevizli Baklava** | Türk Mutfağı | Tatlı | `recipe--ev-yapimi-cevizli-baklava-PMZ33.png` |
| 236 | `[ ] Boşta` | - | **Ev Yapımı Cevizli Baklava** | Türk Mutfağı | Tatlı | `recipe--ev-yapimi-cevizli-baklava-14NT8.png` |
| 237 | `[ ] Boşta` | - | **Geleneksel Aşure** | Türk Mutfağı | Tatlı | `recipe--geleneksel-asure-7NCVV.png` |
| 238 | `[ ] Boşta` | - | **Geleneksel Aşure** | Türk Mutfağı | Tatlı | `recipe--geleneksel-asure-7GC1T.png` |
| 239 | `[ ] Boşta` | - | **Geleneksel Kazandibi** | Türk Mutfağı | Tatlı | `recipe--geleneksel-kazandibi-JSA9M.png` |
| 240 | `[ ] Boşta` | - | **Geleneksel Kazandibi** | Türk Mutfağı | Tatlı | `recipe--geleneksel-kazandibi-WQTJ1.png` |
| 241 | `[ ] Boşta` | - | **Hatay Usulü Peynirli Künefe** | Türk Mutfağı | Tatlı | `recipe--hatay-usulu-peynirli-kunefe-4YMS6.png` |
| 242 | `[ ] Boşta` | - | **Hatay Usulü Peynirli Künefe** | Türk Mutfağı | Tatlı | `recipe--hatay-usulu-peynirli-kunefe-08GXG.png` |
| 243 | `[ ] Boşta` | - | **Kadayıf Dolması** | Türk Mutfağı | Tatlı | `recipe--kadayif-dolmasi-K5YN0.png` |
| 244 | `[ ] Boşta` | - | **Kadayıf Dolması** | Türk Mutfağı | Tatlı | `recipe--kadayif-dolmasi-65JZF.png` |
| 245 | `[ ] Boşta` | - | **Karamelli Trileçe** | Türk Mutfağı | Tatlı | `recipe--karamelli-trilece-1XT8H.png` |
| 246 | `[ ] Boşta` | - | **Karamelli Trileçe** | Türk Mutfağı | Tatlı | `recipe--karamelli-trilece-P477G.png` |
| 247 | `[ ] Boşta` | - | **Kaymaklı Fırın Ayva Tatlısı** | Türk Mutfağı | Tatlı | `recipe--kaymakli-firin-ayva-tatlisi-RATS3.png` |
| 248 | `[ ] Boşta` | - | **Kaymaklı Fırın Ayva Tatlısı** | Türk Mutfağı | Tatlı | `recipe--kaymakli-firin-ayva-tatlisi-GJFFR.png` |
| 249 | `[ ] Boşta` | - | **Kekli Ev Yapımı Supangle** | Türk Mutfağı | Tatlı | `recipe--kekli-ev-yapimi-supangle-08SD2.png` |
| 250 | `[ ] Boşta` | - | **Kekli Ev Yapımı Supangle** | Türk Mutfağı | Tatlı | `recipe--kekli-ev-yapimi-supangle-8Y65M.png` |
| 251 | `[ ] Boşta` | - | **Kilis Cennet Çamuru Tatlısı** | Türk Mutfağı | Tatlı | `recipe--kilis-cennet-camuru-tatlisi-NEJG3.png` |
| 252 | `[ ] Boşta` | - | **Kilis Cennet Çamuru Tatlısı** | Türk Mutfağı | Tatlı | `recipe--kilis-cennet-camuru-tatlisi-BPK9C.png` |
| 253 | `[ ] Boşta` | - | **Limonlu Semifreddo** | İtalyan Mutfağı | Tatlı | `recipe--limonlu-semifreddo.png` |
| 254 | `[ ] Boşta` | - | **Narlı ve Cevizli Sütlü Güllaç** | Türk Mutfağı | Tatlı | `recipe--narli-ve-cevizli-sutlu-gullac-XXR0P.png` |
| 255 | `[ ] Boşta` | - | **Narlı ve Cevizli Sütlü Güllaç** | Türk Mutfağı | Tatlı | `recipe--narli-ve-cevizli-sutlu-gullac-7W9PE.png` |
| 256 | `[ ] Boşta` | - | **Osmanlı Usulü Keşkül-ü Fukara** | Türk Mutfağı | Tatlı | `recipe--osmanli-usulu-keskul-u-fukara-B0D3M.png` |
| 257 | `[ ] Boşta` | - | **Osmanlı Usulü Keşkül-ü Fukara** | Türk Mutfağı | Tatlı | `recipe--osmanli-usulu-keskul-u-fukara-R4BY4.png` |
| 258 | `[ ] Boşta` | - | **Revani** | Türk Mutfağı | Tatlı | `recipe--revani.png` |
| 259 | `[ ] Boşta` | - | **Sütlaç** | Türk Mutfağı | Tatlı | `recipe--sutlac.png` |
| 260 | `[ ] Boşta` | - | **Sütlü İrmik Helvası** | Türk Mutfağı | Tatlı | `recipe--sutlu-irmik-helvasi-HJVHF.png` |
| 261 | `[ ] Boşta` | - | **Sütlü İrmik Helvası** | Türk Mutfağı | Tatlı | `recipe--sutlu-irmik-helvasi-90HB4.png` |
| 262 | `[ ] Boşta` | - | **Tahinli ve Cevizli Kabak Tatlısı** | Türk Mutfağı | Tatlı | `recipe--tahinli-ve-cevizli-kabak-tatlisi-EWAMY.png` |
| 263 | `[ ] Boşta` | - | **Tahinli ve Cevizli Kabak Tatlısı** | Türk Mutfağı | Tatlı | `recipe--tahinli-ve-cevizli-kabak-tatlisi-DHAZQ.png` |
| 264 | `[ ] Boşta` | - | **Tereyağlı Un Helvası** | Türk Mutfağı | Tatlı | `recipe--tereyagli-un-helvasi-YQAZV.png` |
| 265 | `[ ] Boşta` | - | **Tereyağlı Un Helvası** | Türk Mutfağı | Tatlı | `recipe--tereyagli-un-helvasi-85NFE.png` |
| 266 | `[ ] Boşta` | - | **Yoğurtlu İncir Tatlısı** | Akdeniz Mutfağı | Tatlı | `recipe--yogurtlu-incir-tatlisi.png` |
| 267 | `[ ] Boşta` | - | **Çikolata Soslu Ev Yapımı Profiterol** | Türk Mutfağı | Tatlı | `recipe--cikolata-soslu-ev-yapimi-profiterol-89RKY.png` |
| 268 | `[ ] Boşta` | - | **Çikolata Soslu Ev Yapımı Profiterol** | Türk Mutfağı | Tatlı | `recipe--cikolata-soslu-ev-yapimi-profiterol-CMJZX.png` |
| 269 | `[ ] Boşta` | - | **Çıtır Tulumba Tatlısı** | Türk Mutfağı | Tatlı | `recipe--citir-tulumba-tatlisi-T9KG5.png` |
| 270 | `[ ] Boşta` | - | **Çıtır Tulumba Tatlısı** | Türk Mutfağı | Tatlı | `recipe--citir-tulumba-tatlisi-JRRNS.png` |
| 271 | `[ ] Boşta` | - | **İzmir Lokma Tatlısı** | Türk Mutfağı | Tatlı | `recipe--izmir-lokma-tatlisi-4FKVK.png` |
| 272 | `[ ] Boşta` | - | **İzmir Lokma Tatlısı** | Türk Mutfağı | Tatlı | `recipe--izmir-lokma-tatlisi-88047.png` |
| 273 | `[ ] Boşta` | - | **İzmir Şambali Tatlısı** | Türk Mutfağı | Tatlı | `recipe--izmir-sambali-tatlisi-0165D.png` |
| 274 | `[ ] Boşta` | - | **İzmir Şambali Tatlısı** | Türk Mutfağı | Tatlı | `recipe--izmir-sambali-tatlisi-78S3S.png` |
| 275 | `[ ] Boşta` | - | **Akdeniz Yeşillikleri Salatası** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--akdeniz-yesillikleri-salatasi-275C5.png` |
| 276 | `[ ] Boşta` | - | **Akdeniz Yeşillikleri Salatası** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--akdeniz-yesillikleri-salatasi-W5PV6.png` |
| 277 | `[ ] Boşta` | - | **Domatesli Biberli Bulgur Pilavı** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--domatesli-biberli-bulgur-pilavi-06JK9.png` |
| 278 | `[ ] Boşta` | - | **Domatesli Biberli Bulgur Pilavı** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--domatesli-biberli-bulgur-pilavi-5BZ71.png` |
| 279 | `[ ] Boşta` | - | **Domatesli Bulgur Pilavı** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--domatesli-bulgur-pilavi-SMAV8.png` |
| 280 | `[ ] Boşta` | - | **Domatesli Bulgur Pilavı** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--domatesli-bulgur-pilavi-EAH3Q.png` |
| 281 | `[ ] Boşta` | - | **Etli Kabak Dolması** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--etli-kabak-dolmasi-1FT37.png` |
| 282 | `[ ] Boşta` | - | **Etli Kabak Dolması** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--etli-kabak-dolmasi-GGMFQ.png` |
| 283 | `[ ] Boşta` | - | **Fırında Karnabahar Graten** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--firinda-karnabahar-graten-5VYFS.png` |
| 284 | `[ ] Boşta` | - | **Fırında Karnabahar Graten** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--firinda-karnabahar-graten-EA8KP.png` |
| 285 | `[ ] Boşta` | - | **Kabak Mücveri** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--kabak-mucveri-CG2S9.png` |
| 286 | `[ ] Boşta` | - | **Kabak Mücveri** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--kabak-mucveri-PJX34.png` |
| 287 | `[ ] Boşta` | - | **Kabak Mücveri** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--kabak-mucveri-VQ388.png` |
| 288 | `[ ] Boşta` | - | **Kabak Mücveri** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--kabak-mucveri-0TSEM.png` |
| 289 | `[ ] Boşta` | - | **Karışık Turşu** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--karisik-tursu-RXCVC.png` |
| 290 | `[ ] Boşta` | - | **Karışık Turşu** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--karisik-tursu-P1MGR.png` |
| 291 | `[ ] Boşta` | - | **Kıymalı Bezelye Yemeği** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--kiymali-bezelye-yemegi-Z867M.png` |
| 292 | `[ ] Boşta` | - | **Kıymalı Bezelye Yemeği** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--kiymali-bezelye-yemegi-T9YX4.png` |
| 293 | `[ ] Boşta` | - | **Kıymalı Karnıyarık** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--kiymali-karniyarik-WXNB7.png` |
| 294 | `[ ] Boşta` | - | **Kıymalı Karnıyarık** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--kiymali-karniyarik-F52JX.png` |
| 295 | `[ ] Boşta` | - | **Kıymalı Patates Oturtma** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--kiymali-patates-oturtma-BA2S3.png` |
| 296 | `[ ] Boşta` | - | **Kıymalı Patates Oturtma** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--kiymali-patates-oturtma-9ZXWF.png` |
| 297 | `[ ] Boşta` | - | **Mercimek Köftesi** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--mercimek-koftesi-S57CM.png` |
| 298 | `[ ] Boşta` | - | **Mercimek Köftesi** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--mercimek-koftesi-VJF2Z.png` |
| 299 | `[ ] Boşta` | - | **Mercimek Köftesi** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--mercimek-koftesi-C3HRT.png` |
| 300 | `[ ] Boşta` | - | **Patlıcan Musakka** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--patlican-musakka-7QKTR.png` |
| 301 | `[ ] Boşta` | - | **Patlıcan Musakka** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--patlican-musakka-R1YKD.png` |
| 302 | `[ ] Boşta` | - | **Patlıcan Musakka** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--patlican-musakka-X6JEN.png` |
| 303 | `[ ] Boşta` | - | **Portakallı Zeytinyağlı Kereviz** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--portakalli-zeytinyagli-kereviz-VB4QP.png` |
| 304 | `[ ] Boşta` | - | **Portakallı Zeytinyağlı Kereviz** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--portakalli-zeytinyagli-kereviz-Q3JG5.png` |
| 305 | `[ ] Boşta` | - | **Sote Taze Fasulye** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--sote-taze-fasulye-3CRST.png` |
| 306 | `[ ] Boşta` | - | **Sote Taze Fasulye** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--sote-taze-fasulye-S96G1.png` |
| 307 | `[ ] Boşta` | - | **Sote Taze Fasulye** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--sote-taze-fasulye-60MY5.png` |
| 308 | `[ ] Boşta` | - | **Türlü Sebze Yemeği** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--turlu-sebze-yemegi-Y8XQX.png` |
| 309 | `[ ] Boşta` | - | **Türlü Sebze Yemeği** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--turlu-sebze-yemegi-PWPNA.png` |
| 310 | `[ ] Boşta` | - | **Yumurtalı Ispanak Yemeği** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--yumurtali-ispanak-yemegi-JWKGN.png` |
| 311 | `[ ] Boşta` | - | **Yumurtalı Ispanak Yemeği** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--yumurtali-ispanak-yemegi-5HQX7.png` |
| 312 | `[ ] Boşta` | - | **Yumurtalı Ispanak Yemeği** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--yumurtali-ispanak-yemegi-8QXNJ.png` |
| 313 | `[ ] Boşta` | - | **Zeytinyağlı Barbunya Pilaki** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--zeytinyagli-barbunya-pilaki-3QMA1.png` |
| 314 | `[ ] Boşta` | - | **Zeytinyağlı Barbunya Pilaki** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--zeytinyagli-barbunya-pilaki-800AR.png` |
| 315 | `[ ] Boşta` | - | **Zeytinyağlı Biber Dolması** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--zeytinyagli-biber-dolmasi-ZHBA7.png` |
| 316 | `[ ] Boşta` | - | **Zeytinyağlı Biber Dolması** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--zeytinyagli-biber-dolmasi-JXCA1.png` |
| 317 | `[ ] Boşta` | - | **Zeytinyağlı Enginar** | Akdeniz Mutfağı | Zeytinyağlı / Sebze | `recipe--zeytinyagli-enginar.png` |
| 318 | `[ ] Boşta` | - | **Zeytinyağlı Havuçlu Pırasa** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--zeytinyagli-havuclu-pirasa-JQZWQ.png` |
| 319 | `[ ] Boşta` | - | **Zeytinyağlı Havuçlu Pırasa** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--zeytinyagli-havuclu-pirasa-096E8.png` |
| 320 | `[ ] Boşta` | - | **Zeytinyağlı Ispanak Kökü Yemeği** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--zeytinyagli-ispanak-koku-yemegi-HS2RM.png` |
| 321 | `[ ] Boşta` | - | **Zeytinyağlı Ispanak Kökü Yemeği** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--zeytinyagli-ispanak-koku-yemegi-5CS0E.png` |
| 322 | `[ ] Boşta` | - | **Zeytinyağlı Lahana Sarması** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--zeytinyagli-lahana-sarmasi-HD18H.png` |
| 323 | `[ ] Boşta` | - | **Zeytinyağlı Lahana Sarması** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--zeytinyagli-lahana-sarmasi-4WSFQ.png` |
| 324 | `[ ] Boşta` | - | **Zeytinyağlı Lahana Sarması** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--zeytinyagli-lahana-sarmasi-BYYES.png` |
| 325 | `[ ] Boşta` | - | **Zeytinyağlı Taze Börülce** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--zeytinyagli-taze-borulce-JX1S3.png` |
| 326 | `[ ] Boşta` | - | **Zeytinyağlı Taze Börülce** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--zeytinyagli-taze-borulce-2DWCQ.png` |
| 327 | `[ ] Boşta` | - | **Zeytinyağlı Taze Fasulye** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--zeytinyagli-taze-fasulye-AEC33.png` |
| 328 | `[ ] Boşta` | - | **Zeytinyağlı Taze Fasulye** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--zeytinyagli-taze-fasulye-5T46W.png` |
| 329 | `[ ] Boşta` | - | **Zeytinyağlı Yaprak** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--zeytinyagli-yaprak-XWXSC.png` |
| 330 | `[ ] Boşta` | - | **Zeytinyağlı Yaprak** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--zeytinyagli-yaprak-GRP6G.png` |
| 331 | `[ ] Boşta` | - | **Şehriyeli Tereyağlı Pirinç Pilavı** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--sehriyeli-tereyagli-pirinc-pilavi-3ANDW.png` |
| 332 | `[ ] Boşta` | - | **Şehriyeli Tereyağlı Pirinç Pilavı** | Türk Mutfağı | Zeytinyağlı / Sebze | `recipe--sehriyeli-tereyagli-pirinc-pilavi-220KY.png` |
| 333 | `[ ] Boşta` | - | **Balkabağı Çorbası** | Türk Mutfağı | Çorba | `recipe--balkabagi-corbasi-8P04Z.png` |
| 334 | `[ ] Boşta` | - | **Balkabağı Çorbası** | Türk Mutfağı | Çorba | `recipe--balkabagi-corbasi-XC0NG.png` |
| 335 | `[ ] Boşta` | - | **Brokoli Çorbası** | Türk Mutfağı | Çorba | `recipe--brokoli-corbasi-T03F5.png` |
| 336 | `[ ] Boşta` | - | **Brokoli Çorbası** | Türk Mutfağı | Çorba | `recipe--brokoli-corbasi-QEVTH.png` |
| 337 | `[ ] Boşta` | - | **Düğün Çorbası** | Türk Mutfağı | Çorba | `recipe--dugun-corbasi-X5GMB.png` |
| 338 | `[ ] Boşta` | - | **Düğün Çorbası** | Türk Mutfağı | Çorba | `recipe--dugun-corbasi-BKM95.png` |
| 339 | `[ ] Boşta` | - | **Ezogelin Çorbası** | Türk Mutfağı | Çorba | `recipe--ezogelin-corbasi-G2TQP.png` |
| 340 | `[ ] Boşta` | - | **Ezogelin Çorbası** | Türk Mutfağı | Çorba | `recipe--ezogelin-corbasi-VXMA2.png` |
| 341 | `[ ] Boşta` | - | **Havuç Çorbası** | Türk Mutfağı | Çorba | `recipe--havuc-corbasi-6V8WW.png` |
| 342 | `[ ] Boşta` | - | **Havuç Çorbası** | Türk Mutfağı | Çorba | `recipe--havuc-corbasi-N9C65.png` |
| 343 | `[ ] Boşta` | - | **Klasik Kırmızı Mercimek Çorbası** | Türk Mutfağı | Çorba | `recipe--klasik-kirmizi-mercimek-corbasi-XZKA4.png` |
| 344 | `[ ] Boşta` | - | **Klasik Kırmızı Mercimek Çorbası** | Türk Mutfağı | Çorba | `recipe--klasik-kirmizi-mercimek-corbasi-A767J.png` |
| 345 | `[ ] Boşta` | - | **Kremalı Mantar Çorbası** | Türk Mutfağı | Çorba | `recipe--kremali-mantar-corbasi-CNRD2.png` |
| 346 | `[ ] Boşta` | - | **Kremalı Mantar Çorbası** | Türk Mutfağı | Çorba | `recipe--kremali-mantar-corbasi-WZGZ4.png` |
| 347 | `[ ] Boşta` | - | **Kremalı Patates Çorbası** | Türk Mutfağı | Çorba | `recipe--kremali-patates-corbasi-JMEH1.png` |
| 348 | `[ ] Boşta` | - | **Kremalı Patates Çorbası** | Türk Mutfağı | Çorba | `recipe--kremali-patates-corbasi-QMNFW.png` |
| 349 | `[ ] Boşta` | - | **Mercimek Çorbası** | Türk Mutfağı | Çorba | `recipe--mercimek-corbasi.png` |
| 350 | `[ ] Boşta` | - | **Pancar Çorbası** | Türk Mutfağı | Çorba | `recipe--pancar-corbasi-G0E2Q.png` |
| 351 | `[ ] Boşta` | - | **Pancar Çorbası** | Türk Mutfağı | Çorba | `recipe--pancar-corbasi-453Q4.png` |
| 352 | `[ ] Boşta` | - | **Pirinçli Tavuk Çorbası** | Türk Mutfağı | Çorba | `recipe--pirincli-tavuk-corbasi.png` |
| 353 | `[ ] Boşta` | - | **Soğan Çorbası** | Türk Mutfağı | Çorba | `recipe--sogan-corbasi-ST5NS.png` |
| 354 | `[ ] Boşta` | - | **Soğan Çorbası** | Türk Mutfağı | Çorba | `recipe--sogan-corbasi-T5C9Y.png` |
| 355 | `[ ] Boşta` | - | **Tarhana Çorbası** | Türk Mutfağı | Çorba | `recipe--tarhana-corbasi.png` |
| 356 | `[ ] Boşta` | - | **Tavuklu Erişte Çorbası** | Türk Mutfağı | Çorba | `recipe--tavuklu-eriste-corbasi-GBKKM.png` |
| 357 | `[ ] Boşta` | - | **Tavuklu Erişte Çorbası** | Türk Mutfağı | Çorba | `recipe--tavuklu-eriste-corbasi-CCXT5.png` |
| 358 | `[ ] Boşta` | - | **Yayla Çorbası** | Türk Mutfağı | Çorba | `recipe--yayla-corbasi-NCHEP.png` |
| 359 | `[ ] Boşta` | - | **Yayla Çorbası** | Türk Mutfağı | Çorba | `recipe--yayla-corbasi-NT594.png` |
| 360 | `[ ] Boşta` | - | **Yeşil Mercimek Çorbası** | Türk Mutfağı | Çorba | `recipe--yesil-mercimek-corbasi-44JT3.png` |
| 361 | `[ ] Boşta` | - | **Yeşil Mercimek Çorbası** | Türk Mutfağı | Çorba | `recipe--yesil-mercimek-corbasi-ECNCM.png` |
| 362 | `[ ] Boşta` | - | **Yoğurtlu Buğday Çorbası** | Türk Mutfağı | Çorba | `recipe--yogurtlu-bugday-corbasi.png` |
| 363 | `[ ] Boşta` | - | **İşkembe Çorbası** | Türk Mutfağı | Çorba | `recipe--iskembe-corbasi-MDJZS.png` |
| 364 | `[ ] Boşta` | - | **İşkembe Çorbası** | Türk Mutfağı | Çorba | `recipe--iskembe-corbasi-41TR2.png` |
| 365 | `[ ] Boşta` | - | **Şehriyeli Tavuk Suyu Çorbası** | Türk Mutfağı | Çorba | `recipe--sehriyeli-tavuk-suyu-corbasi-PP1AQ.png` |
| 366 | `[ ] Boşta` | - | **Şehriyeli Tavuk Suyu Çorbası** | Türk Mutfağı | Çorba | `recipe--sehriyeli-tavuk-suyu-corbasi-0Y4TN.png` |
