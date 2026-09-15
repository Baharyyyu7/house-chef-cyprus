# OpenCode oturumu: SEO/GEO denetim, düzeltme ve deploy - 2026-09-15

Diğer ajanların (Claude Code, Codex, Antigravity) tanıması için kimlik ve
icraat kaydı. Konum: `.claude/completions/` (oturum tamamlanma raporu).

## Kimlik

- **Ajan:** OpenCode kodlama ajanı, Muse Spark modeli.
- **Rol şeridi:** Claude Code alanı (uygulama kodu, test, deploy).
  Görsel/çeviri/yayın alanlarına dokunulmadı.
- **Oturum:** 2026-09-15 ~09:00-09:15 (+03:00), `visual-redesign-v2` kolu.
- **Talimat:** GEO/SEO/backlink çalışması + deploy ("para harcama" yasağıyla),
  ardından commit+deploy ve fallback temizliği için açık onay.

## Politika çizgileri

Black-hat yok (link satın alma, ağ, spam, cloaking), ücretli işlem yok,
otomatik yayın/dil açma/toplu onay/dış mesaj yok. `ORGANIK_BUYUME_PLANI.md`
ve `AJAN_CALISMA_KURALLARI.md` geçerli.

## Ölçülen durum

| | Yerel | Üretim (deploy öncesi) | Üretim (deploy sonrası) |
|---|---|---|---|
| Skor | 0 (2293 hata: kalitesiz test verisi) | 100 | 0 (119 hata, aşağıda) |
| Sitemap tarif/blog | 0 / 97 | 105 / 247 | 105 / 129 |

## Düzeltmeler (94fba00 içinde yayında)

1. **HTML hreflang hatası:** `layouts/site.blade.php:22` içerik geçmiyordu;
   çevirisiz sayfada `/en/` hreflang basılıp 404'e işaret ediyordu.
   `seoContent` prop eklendi; `recipes/show.blade.php:1` ve
   `blog/show.blade.php:1` içeriği geçiyor.
2. **Sitemap robots filtresi:** `SitemapController::indexable()` `none`
   takma adını ve global kapamayı kaçırıyordu (`LIKE '%none%'`
   `max-image-preview:none` yüzünden kullanılamaz). `effectiveAllows()`
   ile `SeoMetadataService::robots()` efektif değerine göre süzülüyor.
3. **robots.txt senkronu:** `public/robots.txt` + `robots.blade.php` içinde
   PerplexityBot/Applebot/Applebot-Extended/OAI-SearchBot Allow.

## Doğrulama

- `MultilingualSeoAlignmentTest` 15/15, `--filter=Seo` 67/67 geçti.
- Kalan hata (başka alan): `BacklinkVerificationTest` 1 mock hatası.

## Güvenlik bulgusu

Ağaçta görülen `/seo/dashboard` rotası auth'suz iç veri (IndexNow anahtarı,
Search Console, backlink portföyü) dönüyordu; yayına alınmadı.
`can:access-admin` arkasına alınmadan deploy edilmemeli. İncelenmeyen diğer
dosyalar: `AutoFixSeoIssues.php`, `SeoBacklinkOutreach.php`,
`BacklinkOutreachProcessor.php`, kökte `indexnow-submit.php`.

## Deploy (GERÇEKLEŞTİ ~09:08)

Push (e5f7eda..8aff03a) → temiz worktree'de paket (55 MB, checksum OK) →
scp uploads/ → yedek `01M2HTW5TNFY0VTQYVB67H8GSM` doğrulandı → dry-run temiz
→ deploy → smoke 9/9 200, DB/backup healthy. Rollback: e5f7eda.
Deploy sonrası 119 blog "editoryal kapı" hatası = katı kapıların kopya
şablon içeriği bilinçli noindex'e almasıdır (içerik silinmedi).
Canlı: sitemap 200, robots 200, llms.txt 200.
Temizlik: `storage/app/public/fallbacks` silindi (583.253 dosya, 2.2 GB →
storage 232 MB); `build-artifacts/` boşaltıldı.
