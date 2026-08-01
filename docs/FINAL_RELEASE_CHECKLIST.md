# V1.0 Release Candidate iç kontrol listesi

## RC kapanış karar çerçevesi

## Milestone B production içerik kapıları

- [x] Public/admin kullanıcı dili `app:production-language-audit` ile denetlenir.
- [x] Tarif ve blog quality profile’ları readiness rule version ve son tarama zamanı ile tutulur.
- [x] Human approval ayrı audit’li aksiyondur; otomatik publish kapalıdır.
- [x] Branded fallback gerçek medya sayılmaz ve readiness üst sınırı uygulanır.
- [ ] 2.340 fact-check konusu gerçek insan kaynak/inceleme operasyonuyla tamamlanacaktır.

- [x] Katalog, public/member/admin feature testleri, API/OpenAPI, güvenlik başlıkları, queue/scheduler, backup/restore komutları ve production build CI'da doğrulanır.
- [x] Deployment, Nginx, queue/scheduler, TLS/Cloudflare, mail, monitoring, smoke ve rollback prosedürleri dokümante edilmiştir.
- [ ] Gerçek production domain/DNS/TLS, VPS, database/Redis, mail sender, off-server backup hedefi ve monitoring secrets yetkili operatör tarafından sağlanacaktır.
- [ ] İnsan editoryal review sonrası gerçek yemek fotoğrafları seçilecektir; branded fallback bu gerçeği gizlemez.

## Stok medya uyumluluğu

- [ ] Unsplash medyası yalnız insan onayından sonra, izinli CDN hotlink’i ve görünür fotoğrafçı/Unsplash attribution ile kullanılmalıdır.
- [ ] Download tracking yalnız final bağlama anında bir kez kaydedilmelidir; arama ve render bunu tetiklemez.

Bu kontrol listesi yalnız `visual-redesign-v2` dalındaki kod hazırlığını tanımlar; production yayını, DNS veya ücretli görsel üretimi başlatmaz.

## Kod ve katalog

- [x] 50 tarif `published` ve `verified` olarak idempotent seed edilir.
- [x] Her tarifte provenance, household/canonical ölçü, nutrition, alerjen ve diyet verisi bulunur.
- [x] Public liste ve API yalnız yayınlanmış/doğrulanmış tarifleri verir.
- [x] Branded fallback zinciri boş veya kırık görsel bırakmaz.
- [x] Private-before-public medya inceleme, attribution, allowlist, MIME ve checksum kontrolleri uygulanır.

## Provider durumu

- [x] Pexels sunucu tarafı provider, güvenli hata kodları, rate-limit haritalaması ve private indirme akışı hazırdır.
- [ ] Canlı Pexels anahtarı doğrulandı. Yerel güvenli deneme `PEXELS_INVALID_KEY` döndürdü; aday taraması başlatılmadı.
- [x] Eden AI adapter/bütçe koruması hazırdır; ücretli üretim kapalıdır.

## Public yüzey ve SEO

- [x] Blog, bilgi, gizlilik ve politika sayfaları çalışır.
- [x] Dynamic metadata, canonical, Open Graph/X Card, robots, sitemap index ve page/recipe/blog/image sitemap’leri vardır.
- [x] Recipe, Article, FAQ, Organization ve WebSite JSON-LD sağlanır; sahte rating eklenmez.
- [x] Yönetim, kimlik doğrulama ve üye alanları noindex’tir.

## Production geçişi (dış erişim gerektirir)

- [ ] Hosting, production database/cache/queue, domain-DNS ve TLS.
- [ ] Production mail sağlayıcı credential ve doğrulanmış gönderici.
- [ ] Production `.env`, off-server backup destination ve gözlemleme DSN’leri.

Bu maddeler sağlanmadan production deploy yapılmaz. Ayrıntılı sıra [VPS deployment rehberinde](VPS_DEPLOYMENT_GUIDE.md), [Nginx örneğinde](NGINX_CONFIGURATION.md), [post-deploy smoke testinde](POST_DEPLOY_SMOKE_TEST.md) ve [rollback planında](ROLLBACK_PLAN.md) yer alır. Main merge ve release tag bu RC kapanışında yapılmaz.
# Controlled Content Factory release guard

## Phase 4 editorial operations guard

- Review batch, evidence ve review pack kayıtları yayın kararı değildir.
- Seed/system reviewer atanamaz; member editorial operasyon route’larına erişemez.
- Critical/high konular insan kararı olmadan kapanmaz.
- Final human approval, ayrı publish işleminden bağımsızdır.
- Onaysız gerçek medya public schema, sitemap veya OG içinde gerçek medya olarak gösterilmez.

- [x] 50 verified/published recipes and 20 published blog posts remain the public baseline.
- [x] 450 recipe and 180 blog records are draft-only, with 18 batches each and 25 topic clusters.
- [x] Drafts are excluded from public listings, API and sitemaps.
- [x] Human approval records a real admin actor and does not publish automatically.
- [x] `content:factory-audit --json` reports publication/verification leakage.
- [x] Phase 2 draft enrichment writes readiness, technical/SEO/originality signals and fact-check issues without publishing or simulating an editor.
- [ ] Fact-check çözümü, gerçek admin editoryal kararı ve seçili yayın takvimi production öncesi kontrollü içerik operasyonudur.
