# Claude Coni günlük çalışma prosedürü

Bu prosedür her gün zamanlanmış Claude oturumunda koşar. Sunucudaki
otomatik döngü (07:20) seçmeyi, iç bağlantıyı ve ölçmeyi yapar; bu oturum
yargı gerektiren işi yapar: arama sonucu metni, içerik boşluğu, backlink
ve GEO araştırması, kusur düzeltme.

## 0. Hazırlık

```bash
SSH="ssh -o BatchMode=yes -p 65002 u584361532@147.93.93.105"
APP="cd ~/domains/housechefcyprus.com/current"
$SSH "$APP && php artisan coni:durum --json"
$SSH "$APP && php artisan app:scheduler-health --json"
```

- `rapor_zamani` bugünün tarihini taşımıyorsa sunucu döngüsü koşmamış:
  `$SSH "$APP && php artisan coni:calis"` ile elle koştur, sebebini ara.
- `docs/claude-coni/LOG.md` son üç girdisini oku: dün ne yapıldı, ne bekliyor.

## 1. Ölç (her dil)

`coni:durum` çıktısında her dil için: `olcum` (28 gün), `odak`,
`adaylar`, `tiklanmayan`, `nushalar`. Dünle kıyas için
`seo_daily_growth` raporunun `delta` alanına bak. Tahmin yazma.

## 2. SERP işi (her dilde en fazla bir sayfa)

Aday: `tiklanmayan` listesinin başı (gösterim ≥ 20, ilk yirmide, sıfır
tıklama) ya da `adaylar` içinde odak olmayan, TO'su beklenenin çok altında
olan sayfa. Odak sayfasına SERP değişikliği yapılmaz; o sayfa zaten iç
bağlantı deneyinde.

1. Sayfayı sunucudan oku: `$SSH "curl -s -A 'Mozilla/5.0' <adres>"`;
   `<title>`, `meta description`, H1, ilk paragraf.
2. Sorguyu bil: sayfanın gerçekten göründüğü aramalar
   `$SSH "$APP && php artisan coni:sorgular '<adres>'"`. Başlık en çok
   gösterim alan sorunun cevabı olmalı.
3. Başlığı ve açıklamayı **o dilde** yaz: sorunun cevabını ya da sayfanın
   verdiği somut şeyi (süre, derece, porsiyon, yöntem) öne koy. Başlık
   15-65 karakter (marka eki ayrıca eklenir), açıklama 70-160.
   Anahtar kelime doldurma yok; tık tuzağı yok; sayfada olmayan şeyi vaat etme.
4. Uygula:
   ```bash
   $SSH "$APP && php artisan coni:serp '<adres>' --baslik='...' --aciklama='...' --neden='<ölçüm + gerekçe>'"
   ```
5. Canlıda doğrula: `$SSH "curl -s <adres> | grep -o '<title>[^<]*'"`.
   Görünmüyorsa önbellek: `$SSH "$APP && php artisan cache:clear"` değil,
   önce sayfanın gerçekten o kaydı okuduğunu kontrol et.
6. Kötü sonuçta: `php artisan coni:geri-al <id>`.

## 2b. Çeviri işi (her gün, en çok gösterim alandan başlayarak)

Yabancı dildeki tarif adımları kalıp çeviriyse (`RecipeTranslationGate`)
sayfa o dilde `noindex` ve site haritası dışıdır. `coni:durum` raporunda her
dilin `ceviri_bekleyen` listesi, aramada görünen ama bu yüzden kapalı olan
tarifleri gösterim sırasıyla verir. Günde en az 5 tarif-dil çifti:

1. Türkçe asılı oku (başlık, özet, açıklama, adım başlıkları, adımlar):
   ```bash
   $SSH "$APP && php artisan tinker" <<'PHP'
   $r = App\Models\Recipe::where('slug','<slug>')->with('steps')->first();
   echo $r->title, "\n", $r->description, "\n";
   foreach ($r->steps->sortBy('position') as $s) echo $s->position, ' ', $s->instruction, "\n";
   PHP
   ```
2. O dilde, anadil okurun kuracağı cümleyle yaz. Yemeğin Türkçe adı
   başlıkta kalır (diasporadan gelen aramalar Türkçe adı yazıyor), yanına
   o dilde ne olduğu. Başlık o dilin "tarif" kelimesini içersin (Recipe,
   Rezept, Przepis, Receita, Рецепт, طرز تهیه), yoksa sistem sona ekliyor.
   Asılda olmayan bilgi ekleme; asıl çelişkiliyse çelişen ayrıntıyı atla.
3. JSON: `{"title","excerpt","description","step_titles":[...],"steps":[...]}`
   (adım sayısı asılla aynı), sunucuya kopyala ve uygula:
   ```bash
   scp -P 65002 <dosya>.json u584361532@147.93.93.105:~/coni-ceviri.json
   $SSH "$APP && php artisan coni:ceviri <slug> <dil> ~/coni-ceviri.json --neden='<gösterim + neden>' && rm ~/coni-ceviri.json"
   ```
   Çıktıda `engel ... → yok (dizine açık)` görülmeli.
4. Canlıda: `curl -s <adres> | grep -o '<meta name="robots"[^>]*>'` → `index`.

## 3. Sayfa sağlığı (odak sayfaları)

Her dilin odak sayfası için dört geçiş:

1. **Erişim:** 200, `index,follow`, kanonik kendisi, hreflang kümesi tam.
   Googlebot UA ile sunucudan kontrol et.
2. **Rakip:** o dilde ana sorguyu ara (WebSearch), ilk sonuçların neyi
   kapsadığını oku. Yalnız okuduğun sayfadan iddia çıkar; kaynağı yaz.
3. **Cevap motorları (GEO):** her başlığın altındaki ilk cümle soruyu
   cevaplıyor mu, bölüm tek başına okunduğunda anlaşılıyor mu? Eksikse
   içerik değişikliği önerisi LOG'a yazılır; içerik gövdesi elle ve tek
   sayfa olarak düzeltilir, toplu değil.
4. **Dönüşüm:** sayfa reklam taşıyor mu, tarif kartları ve iç bağlantılar
   çalışıyor mu?

## 4. Backlink ve marka izi (her gün bir dil, sırayla)

Sıra: tr, en, de, ru, pl, pt, fa (LOG'daki son dilden devam).

1. O dilin odak sayfası konusu için WebSearch ile: sitenin adının geçtiği
   ama bağlantı vermeyen sayfalar; konu kaynak listesi tutan gerçek
   sayfalar (üniversite, belediye, kültür, yemek tarihi); kırık bağlantı
   fırsatı.
2. Her gerçek aday için `docs/claude-coni/outreach/YYYY-MM-DD-<dil>.md`
   içine: kaynak adres, neden uygun, hangi sayfamızı gösteriyoruz,
   o dilde kişisel kısa bir metin taslağı. **Gönderilmez.**
3. Ücret isteyen, alakasız, bağlantı ağı niteliğindeki site aday değildir.
4. Kazanılmış bağlantı ancak üçüncü taraf sayfada canlı görülünce yazılır.

## 5. Kusur

Ölçüm sırasında bir kusur bulursan (ölçüm yanlış, sayfa 404, şema hatalı):
test yaz, düzelt, yalnız kendi dosyalarını `git add <yol>` ile ekle
(`git add -A` yasak; başka ajanların işi var), commit et, yayınla.

Yayın: başka bir `release.sh` koşuyorsa (`ps aux | grep release.sh`) bitmesini
bekle. Kendi worktree'nden:

```bash
git fetch origin visual-redesign-v2
git rebase origin/visual-redesign-v2     # ya da merge --ff-only
php vendor/bin/phpunit <dokunulan testler>
git push origin HEAD:visual-redesign-v2
git push origin HEAD:<kendi dalın>
BRANCH=<kendi dalın> scripts/release.sh
```

Yayından sonra canlıda doğrula: değişen sayfa, `app:invariants`,
`seo:ranked-url-guard`.

## 6. Defter

`docs/claude-coni/LOG.md` sonuna ekle (hiç silme):

```
## YYYY-MM-DD
- Ölçüm: toplam ve dil dil tıklama/gösterim, dünle fark.
- Sonuçlanan: #id dil tür sayfa → kazanç/kayıp/nötr (tıklama farkı).
- Bugün yapılan: #id dil tür sayfa, önce → sonra, neden.
- Backlink/GEO: dil, bulunan aday sayısı, taslak dosyası.
- Kusur ve düzeltme: commit.
- Bekleyen: ne zaman ölçülecek.
```
