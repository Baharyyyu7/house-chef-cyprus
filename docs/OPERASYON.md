# Operasyon

Canlı site: https://housechefcyprus.com. Bu belge sitenin bugün nasıl çalıştığını anlatır. `docs/` altındaki
VPS ve "go-live" belgeleri planlama dönemindendir; çeliştiklerinde bu belge geçerlidir.

## Ortam

- Hostinger paylaşımlı barındırma (LiteSpeed, lsphp, hcdn CDN), MySQL.
- SSH: `ssh -p 65002 u584361532@147.93.93.105`
- `APP_HOME=~/domains/housechefcyprus.com`: `releases/<sha>/`, `shared/` (.env, storage, logs), `current` (bağlantı).
  `public_html/index.php` `../current`'i yükler; `public_html/storage` → `shared/storage/app/public`.
- PHP: `/opt/alt/php84/usr/bin/php`.

## Dallar

**Üretim dalı `visual-redesign-v2`.** `main` yüzlerce commit geridedir; ondan yayın yapmak siteyi eski tasarıma döndürür
ve duman testi bunu yakalamaz. Claude worktree'leri varsayılan olarak `main`'den açılır; iş öncesi
`origin/visual-redesign-v2`'ye geç.

## Yayın (deploy)

```bash
scripts/release.sh
```

Betik, mevcut dalın `visual-redesign-v2` olmasını ve `origin`'deki uçla aynı olmasını ister. Temiz bir
`/tmp/hc-release-<sha>` worktree'sinde composer/npm kurar, derler, tam test paketini (`php vendor/bin/phpunit`)
koşar, artefaktı sunucuya gönderip `scripts/deploy-hostinger-release.sh`'i `current/` içinden çalıştırır.

Testleri aynı SHA'da zaten koştuysan `--skip-tests` kullanabilirsin (tam paket ~35 dk; uzun koşu sırasında dal ucu
kayarsa yayın reddedilir).

## Bilinen tuzaklar

1. **`php artisan test` kullanma.** Sarmalayıcı SIGTERM alıyor; her zaman `php vendor/bin/phpunit`.
2. **Yayından sonra lsphp'yi yeniden başlat.** LiteSpeed opcache eski sürümü tutar: `pkill -f lsphp`. Doğrulama:
   ana sayfadaki CSS hash'i `public/build/manifest.json` ile aynı olmalı.
3. **REPOSITORY_URL** `git@github-housechef:Baharyyyu7/house-chef-cyprus.git` olmalı (SSH takma adı).
4. **Yarım kalan deneme** `releases/<sha>/` bırakır; betik o SHA'yı yeniden kurmayı reddeder. Klasörü sil.
5. **Geri alma** (`rollback-hostinger-release.sh`) siteyi bakım modunda bırakabilir. Sonra elle
   `artisan up` ve `config:cache route:cache view:cache event:cache`.
6. **HTTP bağlamında `Artisan::call`** üretimde özel komutları bulamayabilir; zamanlayıcı mantığını servise koy.

## Zamanlayıcı

Hostinger cron'u güvenilmez. Cloudflare Worker `hc-zamanlayici` (`infra/cloudflare/hc-zamanlayici/`,
`wrangler deploy`) dakikada bir `/_zamanlayici/{token}` ucunu çağırır. Token sunucu `.env`'inde
`SCHEDULER_TRIGGER_TOKEN` ve Worker secret'ında aynı değerdir; ikisi birlikte değiştirilir.

Sağlık: `php artisan app:scheduler-health --json` (`last_started_at` bir dakikadan taze olmalı) ve `/health`.

## İçerik yayın kuralları

- Yayına geçiş model düzeyinde korunur (`App\Models\Concerns\GuardsPublication`): üretimde blog yazısı kayıtlı
  insan onayı (`human_approved_at` + `human_approved_by_user_id`) olmadan yayına alınamaz; blog ve tarif için günlük
  tavan `DAILY_PUBLICATION_CAP` (varsayılan 10).
- Yönetimdeki "Hazır olanları yayınla" düğmesi tek seferde en çok 5 yazı yayınlar.
- Blog gövdesi Markdown'dır; ham HTML sayfada silinir. HTML gövdeyi `php artisan blog:html-govde-cevir` ile bul,
  `--apply` ile yedek alıp çevir.
- İndeksleme kapısı (`BlogIndexability`) okurun gördüğü metni ölçer ve yazı içi cümle tekrarını, yer tutucuları,
  ham HTML'i engeller.
- Toplu içerik üreten ya da onay alanını kendisi dolduran komut/seeder ekleme. 16 Ağustos 2026'da ~3200 makine
  sayfası bu yoldan yayına çıktı ve site ceza aldı.

## Yedek

Yedekler deploy sırasında alınır ve doğrulanır. Üretimde restore testi yapılmaz. Ayrıntı:
`docs/BACKUP_RESTORE_OPERATIONS.md`.

## Doğrulama listesi (her yayından sonra)

- `curl -sI https://housechefcyprus.com/` → 200, `/health` → `ok`.
- Ana sayfadaki `build/assets/app-*.css` adı manifest'tekiyle aynı.
- Bir tarif, bir blog yazısı, `/en/` ana sayfası ve `sitemap.xml` 200.
