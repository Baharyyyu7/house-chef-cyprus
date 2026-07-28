# Admin V3 Tasarım Sistemi

Admin V3, public markadan ayrı bir operasyonel yüzeydir: light modda ivory yüzeyler, dark modda siyah/antrasit yüzeyler ve etkileşimlerde turuncu vurgu kullanır. Yeşil, yalnız sistem sağlığı gibi anlamsal durumlarda kullanılır; marka rengi değildir.

## Shell

- Masaüstünde daraltılabilen, grup bazlı sidebar; mobilde Escape, dışarı tıklama ve scroll lock destekli drawer.
- Topbar: breadcrumb, sayfa başlığı, tema anahtarı, profil kısayolu ve yalnız çalışan “Yeni tarif” aksiyonu.
- Gruplar, yalnız tanımlı route'lara bağlanır: Genel, İçerik, Malzeme verisi, Beslenme ve güven, Sistem.

## Primitifler

`hc-v3-card`, `hc-v3-btn`, `hc-v3-badge`, `hc-v3-empty`, dashboard metric/attention/activity sınıfları semantic token kullanır. 44 px minimum dokunma alanı, görünür focus ring ve `prefers-reduced-motion` zorunludur.

Part 1 yalnız shell ve dashboard'u dönüştürür. CRUD ekranlarının tablo/form tasarımı sonraki admin teslimlerinde ele alınır.
