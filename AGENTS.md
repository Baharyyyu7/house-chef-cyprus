# AGENTS.md

Bu depoda Claude Code, Codex ve Antigravity birlikte çalışıyor.

**Herhangi bir dosya üretmeden önce şunu oku:
[docs/AJAN_CALISMA_KURALLARI.md](docs/AJAN_CALISMA_KURALLARI.md)**

Orada "kim neye dokunur" ve üretilen dosyanın nereye yazılacağı tanımlı.
Kısa özeti:

- **Görseller yerel diske indirilmez**, doğrudan sunucudaki
  `shared/storage/app/media-import/recipes/` klasörüne gider.
  Dosya adı `recipe--{slug}.png` olmak zorunda.
- **`recipe-images:generate-fallbacks` yerelde çalıştırılmaz.** Bu komut bir
  kez 1.205.410 dosya üretip diski doldurdu.
- **`build-artifacts/` deploy doğrulandıktan sonra aynı oturumda silinir.**
- **Yerel yedekler saklanmaz**, gerçek yedekler sunucuda üretilir.
- **Medyada toplu onay yok.** Kimlik eşleşmesi geçmeyen görsel yayına girmez.
- Silme sırası her zaman: **önce yükle → doğrula → sonra sil.**

Kod yazım kuralları için [CLAUDE.md](CLAUDE.md) geçerlidir.
