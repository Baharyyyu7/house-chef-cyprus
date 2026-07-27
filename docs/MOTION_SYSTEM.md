# Global Motion System — V3 Plan

## Tokenlar

| Token | Hedef |
| --- | --- |
| `--hc-motion-fast` | mikro feedback: 120ms |
| `--hc-motion-normal` | button/card/dropdown: 180–220ms |
| `--hc-motion-slow` | page/section: 300–420ms |
| `--hc-ease-enter` | sakin giriş cubic-bezier |
| `--hc-ease-exit` | hızlı çıkış cubic-bezier |
| `--hc-ease-spring` | küçük lift/scale, bounce'suz |

Faz 1'de tokenlar `resources/css/app.css` içinde tek kaynaktan tanımlanır. Mevcut `v2-reveal`, hero scale, card hover ve button press bu sözleşmeye taşınır; tüm sayfalara aynı anda rastgele class eklenmez.

## Uygulama matrisi

| Etkileşim | Davranış | Guardrail |
| --- | --- | --- |
| Page/section/card stagger | opacity + küçük translate | İçerik hiç bekletilmez |
| Image zoom / card lift | hover scale/lift | Layout shift yok |
| Button/favorite/filter chip | kısa press/state | Aksiyon tıklaması önceliklidir |
| Toast/modal/dropdown/drawer/tab | enter/exit | Focus, escape ve aria önce gelir |
| Form validation | renk + kısa shake yerine açıklama | Renk tek sinyal değildir |
| Skeleton/loading/chart | düşük kontrast shimmer veya statik | Reduced motion statik görünüm |
| Theme transition | yalnız renk/border/shadow | Flash, uzun fade veya okunaksız ara durum yok |
| Metric count-up | gerçek sayıya kadar kısa artış | Veri yoksa çalışmaz |

## Reduced motion

`prefers-reduced-motion: reduce` altında animation ve transition süreleri pratikte sıfıra iner; drawer/modal görünürlük ve focus davranışı korunur. Parallax, auto-playing video, sürekli dönen ikon veya bilgi saklayan animation kullanılmaz.

## Mevcut açıklar

Motion V2 home/header ile sınırlı. Toast, modal, dropdown, tab, filters, loading/skeleton, form validation, favorite state, cooking timer, admin table/chart ve auth akışlarında merkezi sözleşme yoktur. Faz 1 bunları component seviyesinde tanımlar; sayfa uygulamaları ilgili fazlara bırakılır.
