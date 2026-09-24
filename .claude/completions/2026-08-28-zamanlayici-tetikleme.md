# Zamanlayıcı tetikleme - 2026-08-28

## Sorun

Hostinger'ın cron servisi bu hesapta 2026-08-02 22:01'den beri hiçbir iş
çalıştırmadı. `shared/logs/scheduler.log` 121 byte ve o tarihte donmuş,
`queue-worker.log` 0 byte. Komut elle koşturulduğunda `status: healthy`
dönüyor, yani uygulama tarafında sorun yok. Yeniden kurulan saatlik iş de
tetiklenmedi. 26 gündür zamanlanmış blog yayınları, sitemap tazeleme, kuyruk
işleri ve yedekler koşmuyordu.

## Yapılan

- `SchedulerTriggerController` + `/_zamanlayici/{token}` ucu (commit f77fa04).
  Token yoksa 404, yanlış token 404 (ucun varlığını sızdırmamak için),
  `throttle:6,1`, `Cache::lock` ile paralel koşma engelli. 7 test.
- Sunucu `.env`'ine 64 karakterlik `SCHEDULER_TRIGGER_TOKEN`.
- Cloudflare Worker `hc-zamanlayici`: `HEDEF` secret'ı (tam adres, token dahil),
  cron tetiği `* * * * *`.
- Worker kaynağı depoya alındı: `infra/cloudflare/hc-zamanlayici/` (commit 6a0be7c).

## Açık kalan

**Worker'ın kodu henüz yüklenmedi.** Panelin gömülü VS Code editörü
cross-origin iframe; tıklama ve klavye olayları içeri geçmiyor, `monaco`
nesnesine erişilemiyor. Wrangler CLI çalışır ama OAuth onayı gerekiyor ve o
onay verilmedi. Kod yüklenene kadar cron tetiği boşa dönüyor - zamanlayıcı
hâlâ ölü.

İki yoldan biri: `npx wrangler login` + Authorize, sonra
`cd infra/cloudflare/hc-zamanlayici && npx wrangler deploy`; ya da kodu panele
elle yapıştırmak.

Ayrıca Hostinger'ın bozuk cron servisi için destek kaydı açılmadı - asıl arıza
orada, Worker sadece etrafından dolaşıyor.

## Yan bulgular

- Tam test paketi: 766/766 geçti, 4603 assertion.
- Yayındaki 134 görselin hepsi makine damgalı onay taşıyor (insan onayı yok).
  Rastgele 6'sı gözle kontrol edildi, 6/6 doğru yemek; geri çekilmediler.
- Dosya yolundan slug eşleştirme testi geçersiz: depolama ULID tabanlı.
