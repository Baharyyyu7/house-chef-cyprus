# AGENTS.md

Bu depoda Claude Code, Codex ve Antigravity birlikte çalışıyor.

**Herhangi bir dosya üretmeden önce şunu oku:
[docs/AJAN_CALISMA_KURALLARI.md](docs/AJAN_CALISMA_KURALLARI.md)**

Orada "kim neye dokunur" ve üretilen dosyanın nereye yazılacağı tanımlı.
Kısa özeti:

- **Görseller yerel diske indirilmez**, doğrudan sunucudaki
  `shared/storage/app/media-import/recipes/` klasörüne gider.
  Dosya adı `recipe--{slug}.png` olmak zorunda.
  Görsel üretmeden önce [docs/GORSEL_URETIM_MASTER_PROMPT.md](docs/GORSEL_URETIM_MASTER_PROMPT.md)
  okunur: ölçü alt sınırı, ad kalıbı ve reddedilme sebepleri orada.
  Son teslimatta 141 görselin 92'si bu kurallara uymadığı için içeri
  alınamadı.
- **SEO blog yazıları da yerel diske yazılmaz**, sunucudaki
  `shared/storage/app/content-import/blog/{slug}.json` klasörüne gider.
  Teslim klasöründe PHP dosyası çalıştırılmaz veya içe alınmaz.
  Kurallar, anahtar kelime araştırması ve dil stratejisi:
  [docs/GOREV_SEO_BLOG_URETIMI.md](docs/GOREV_SEO_BLOG_URETIMI.md)
- **İçerik çevirileri** sunucudaki
  `shared/storage/app/content-import/translations/{dil}/` klasörüne gider,
  yalnız JSON. Kurallar: [docs/GOREV_CEVIRI_URETIMI.md](docs/GOREV_CEVIRI_URETIMI.md)
- **Hiçbir ajan dil etkinleştirmez.** `config/locales.php` içindeki `enabled`
  alanına dokunulmaz. Çevirisi olmayan kayıt o dilin sitesinde yoktur;
  yarım çeviriyle dil açmak site geneli kalite sinyalini düşürür.
- **Hiçbir ajan yazı yayınlamaz.** Teslim edilen içerik `draft` olarak açılır;
  yayın kararı insana aittir. 2026-08-16'da ~3.200 makine üretimi sayfa toplu
  yayına çıktı ve arama sonuçlarını doldurdu.
- **`recipe-images:generate-fallbacks` yerelde çalıştırılmaz.** Bu komut bir
  kez 1.205.410 dosya üretip diski doldurdu.
- **`build-artifacts/` deploy doğrulandıktan sonra aynı oturumda silinir.**
- **Yerel yedekler saklanmaz**, gerçek yedekler sunucuda üretilir.
- **Medyada toplu onay yok.** Kimlik eşleşmesi geçmeyen görsel yayına girmez.
- Silme sırası her zaman: **önce yükle → doğrula → sonra sil.**

Kod yazım kuralları için [CLAUDE.md](CLAUDE.md) geçerlidir.
