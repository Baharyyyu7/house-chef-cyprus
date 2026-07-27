# Design System V3

## Amaç ve geçiş yaklaşımı

V3, mevcut V2 public deneyiminin üstüne kurulan merkezi semantic UI katmanıdır. Rota, domain veya API davranışını değiştirmez. Eski `hc-*` sınıfları geriye uyumludur; yeni ve güncellenen ekranlar V3 primitive'lerine taşınır.

## Semantic tokens

`resources/css/app.css` light/dark için aşağıdaki grupları tanımlar: background (`page/subtle/muted/inverse`), surface (`default/raised/overlay/interactive/selected`), text (`primary/secondary/muted/inverse/accent/danger/warning/success/info`), border, brand, status, shadow, radius ve 4 tabanlı spacing scale.

Renk değeri Blade dosyasında yazılmaz. Public, member ve admin bileşenleri semantic token veya V3 class kullanır. Legacy `forest/gold` Tailwind alias'ları geçiş sürecinde nötr/turuncu eşdeğerlerine bağlıdır; yeni kullanım değildir.

## Typography

`display-xl`, `display-lg`, `heading-1..4`, `body-lg/md/sm`, `label-lg/md`, `caption`, `overline` ve `code` class'ları aynı Manrope/Inter yığınıyla çalışır. Türkçe karakterler web font fallback'i ve sistem sans ile desteklenir; repository'ye font dosyası eklenmez.

## Light/dark contract

Tema `localStorage.theme` ile korunur; semantic tokens `html.dark` altında değişir. Primitive'lerde hard-coded yüzey/metin rengi kullanılmaz. Focus, border, disabled, overlay ve input browser controls iki tema için bu tokenlardan beslenir.

## Yasaklar

- Yeni Blade kodunda rastgele hex, emoji navigation veya paket-karışık ikon kullanımı.
- Sadece renk ile status anlatımı.
- Rota hazır değilken footer/bilgi sayfası linki eklemek.
- Aynı anda tüm ekranları yeniden yazmak.

