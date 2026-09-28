# 2026-09-27 AdSense düşük değer temizliği, hareket katmanı, çok dilli tarifler

## Tetik
AdSense: "Müdahale edilmesi gerekiyor - Düşük değere sahip içerik".
Kullanıcı ayrıca sitenin her yerinde After Effects enerjisinde hareket istedi.

## Ölçülen
- 534 yayındaki tarifin 380'i kalite kapısından geçmiyordu (378 ölçüsüz,
  351 şablon küme, 117 yer tutucu); hepsi noindex ama açıktı.
- 45 yayındaki yazı noindex'ti (35'i eşli kopya, 10'u incelenmemiş).
- 6 dilde ~530 tarif sayfası kalıp adım çevirisiydi.
- Yabancı tarif sayfalarında birim, kesir, hazırlık notu Türkçe; 25 arayüz
  dizesi İngilizce kopya; malzeme adları parça değiştirme çöpü.
- Adım zamanlayıcı düğmeleri hiç çalışmıyordu (tırnak hatası).

## Yapılan
- 400 tarif + 45 yazı taslağa (yedekli); 23 kırık tarif bağlantısı onarıldı.
- Kalıp çevirili tarif o dilde 404; otomatik yayın yolu kalite kapısına bağlı.
- resources/js/motion.js: kinetik başlık, 3B kart, eğim, patlama, konfeti,
  okuma çizgisi, sayfalar arası morf. LCP/CLS'e dokunmuyor.
- 28 tarif x 6 dil gerçek çeviri; 220 malzeme adı 6 dilde; birim ve hazırlık
  sözlükleri.

## Commitler
a250242, 31a07a6, c34134a, bd7ffb4 ve alt satır düzeltmesi.

## Kalan
AdSense "İnceleme iste" kullanıcıda. 106 tarifin çevirisi. Taslaktaki talepli
tariflerin yeniden yazımı. Bkz. .claude/TASKS.md.
