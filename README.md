# House Chef Cyprus

Kıbrıs ve Türk mutfağı tarif sitesi: https://housechefcyprus.com (Türkçe ana dil; `/en`, `/de`, `/ru`, `/pl`, `/pt`, `/fa`
çevirileri). Tarifler, malzeme dolabından öneri, ölçü dönüştürme, beslenme ve alerjen bilgisi, blog.

## Teknoloji

Laravel 13 / PHP 8.4, Livewire + Volt, Alpine, Tailwind 3, Vite. Üretimde MySQL, testte SQLite.

## Yerelde çalıştırma

```bash
./setup.sh
```

```bash
./start.sh
```

Uygulama `http://127.0.0.1:8010` üzerinde açılır. Yerel yönetici yalnız local/testing ortamında seed edilir:
`admin@housechef.local` / `ChangeMe123!`.

## Test

```bash
php vendor/bin/phpunit
```

Tam paket ~35 dakika. `php artisan test` kullanma (sarmalayıcı SIGTERM alıyor).

## Dallar ve yayın

Üretim dalı **`visual-redesign-v2`**; `main` eskidir. Yayın `scripts/release.sh` ile yapılır. Sunucu, zamanlayıcı,
tuzaklar ve içerik yayın kuralları: [docs/OPERASYON.md](docs/OPERASYON.md).

## Çok ajanlı çalışma

Bu depoda birden fazla yapay zekâ ajanı çalışır. Kurallar: [AGENTS.md](AGENTS.md),
[docs/AJAN_CALISMA_KURALLARI.md](docs/AJAN_CALISMA_KURALLARI.md). Commit'e yalnız kendi dosyalarını adıyla ekle;
`git add -A` kullanma.
