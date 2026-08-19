# Ajan çalışma kuralları

Bu depoda birden fazla ajan çalışıyor: Claude Code, Codex ve Antigravity.
Bu dosya "kim neye dokunur" ve **"üretilen dosya nereye yazılır"** sorularını
yanıtlar. Herkes için bağlayıcıdır.

Son güncelleme: 19 Ağustos 2026.

---

## 1. Neden bu dosya var

19 Ağustos 2026'da geliştirme makinesinde **228 GB diskin 2,6 GB'ı kalmıştı.**
Sebebi tek bir hata değil, kimsenin sahiplenmediği birikimdi:

| Klasör | Birikmiş | Gerçekte gereken |
| --- | --- | --- |
| `storage/app/public/fallbacks` | 4,6 GB · **1.205.410 SVG** | Canlıda 3.910 dosya (31 MB) |
| `build-artifacts` | 4,6 GB | 0 — hepsi yayında ve git'ten üretilebilir |
| `storage/app/private/backups` | 151 MB | 0 — bunlar *yerel* test veritabanının yedekleri |

Bir milyon dosya yalnız yer kaplamaz: Spotlight, Time Machine ve `git status`
her seferinde hepsini tarar, makine yavaşlar.

**Kural buradan çıkıyor: yeniden üretilebilen hiçbir şey yerelde birikmez.**

---

## 2. Üretilen dosya nereye yazılır

### 2.1 Tarif ve blog görselleri — Antigravity

Görseller **yerel diske indirilmez.** Doğrudan sunucuya gider:

```
u584361532@147.93.93.105:~/domains/housechefcyprus.com/shared/storage/app/media-import/recipes/
```

```bash
scp -P 65002 recipe--*.png \
  u584361532@147.93.93.105:~/domains/housechefcyprus.com/shared/storage/app/media-import/recipes/
```

Dosya adı kuralı değişmedi ve pazarlık konusu değil: `recipe--{slug}.png`.
Slug ile birebir eşleşmezse içe aktarma reddeder. Ölçü en az 1400×850,
önerilen 1600×1350.

Yerelde denemek gerekiyorsa `storage/app/media-import/` kullanılır, **iş
bitince klasör boşaltılır.** Orası bir teslim kutusudur, arşiv değil.

### 2.2 Yer tutucu görseller (fallbacks) — hiç kimse

`recipe-images:generate-fallbacks` komutu **yerelde çalıştırılmaz.** Bir
milyon dosyanın kaynağı buydu. Gerekirse sunucuda çalışır; yerelde çalıştıysa
klasör aynı oturumda silinir:

```bash
rm -rf storage/app/public/fallbacks
```

### 2.3 Deploy paketleri — Claude Code

`build-artifacts/` bir ara çıktıdır. Deploy doğrulandıktan sonra **aynı
oturumda silinir.** Sürüm gerekiyorsa git'te zaten duruyor; paket iki komutla
yeniden üretilir.

### 2.4 Yedekler

`storage/app/private/backups` yereldeyken yalnız test verisidir, saklanmaz.
Gerçek yedekler sunucuda üretilir ve orada durur (`backup:health` ile
denetlenir). Yerel yedeği kimse canlıya taşımaz.

### 2.5 Büyük ikili dosyalar

Video, ham fotoğraf arşivi, zip, tar.gz gibi büyük dosyalar bu depoya **hiç
girmez** — `.gitignore`'a güvenilmez, en baştan başka yere konur. Depo geçmişi
şu an 1,7 GB; şişmesi geri alınamaz.

---

## 3. Sahiplik — kim neye dokunur

| Alan | Sahip | Diğerleri |
| --- | --- | --- |
| Görsel üretimi ve teslimi | Antigravity | Dosya adı kuralını bozmaz |
| Dil paketleri, çeviri altyapısı, RTL | Codex | Claude yalnız çerçeve metinlerini (`lang/*/validation.php` vb.) tamamlar |
| Uygulama kodu, tasarım, test, deploy | Claude Code | — |
| Medya onayı ve yayına alma | Claude Code | Kimlik eşleşmesi geçmeden onay verilmez |

Aynı dosyayı iki ajan aynı anda düzenlemez. Bir alana girmeden önce
`git log --oneline -5` ile son dokunanı gör.

---

## 4. Medya onayında değişmeyen kural

3212 görsel şu anda `kimlik_eslesmesi_zayif` nedeniyle onay bekliyor. Bu bir
kilit değil, korumadır: görselin o tarife ait olduğu kanıtlanamıyor.

**Toplu onay yapılmaz.** Daha önce bir kez yapıldı ve tariflerin üstüne ait
olmadıkları görseller çıktı. Doğru yol, doğru eşleşen yeni görsel üretmektir.

Kontrol için: `php artisan media:verify-pending` (salt okunur rapor).

---

## 5. İş bitiminde kontrol listesi

Her ajan, oturumu kapatmadan önce:

1. `du -sh storage build-artifacts 2>/dev/null` — beklenmedik bir büyüme var mı
2. `git status --short` — takip edilen dosya yanlışlıkla silinmiş mi
3. Ara çıktılar temizlendi mi (fallbacks, build-artifacts, media-import)
4. Test yeşil mi

Silmeden önce **her zaman** hedefe bak. 19 Ağustos'taki temizlikte
`media-import` ve `private` klasörleri komple gitti; içlerinde git'in takip
ettiği 11 `.gitignore` dosyası vardı ve klasör yapısı bozuldu. Veri kaybı
olmadı çünkü silmeden önce sunucuya yüklenmişti — sıra buydu, tersi değil:

**önce yükle → doğrula → sonra sil.**
