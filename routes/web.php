<?php

use App\Http\Controllers\Admin\AiImageStudioController;
use App\Http\Controllers\Admin\AiKitchenController as AdminAiKitchenController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\BlogPostController as AdminBlogPostController;
use App\Http\Controllers\Admin\ContactRequestController as AdminContactRequestController;
use App\Http\Controllers\Admin\ContentFactoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DataQualityController;
use App\Http\Controllers\Admin\EditorialOperationsController;
use App\Http\Controllers\Admin\FailedJobController;
use App\Http\Controllers\Admin\ImageRequestController;
use App\Http\Controllers\Admin\IngredientController as AdminIngredientController;
use App\Http\Controllers\Admin\IntegrationCenterController;
use App\Http\Controllers\Admin\MailOperationsController;
use App\Http\Controllers\Admin\MeasurementController;
use App\Http\Controllers\Admin\MediaController as AdminMediaController;
use App\Http\Controllers\Admin\MediaOperationsController;
use App\Http\Controllers\Admin\NutritionController as AdminNutritionController;
use App\Http\Controllers\Admin\PersonalizationOperationsController;
use App\Http\Controllers\Admin\ProductExperienceController;
use App\Http\Controllers\Admin\ProductionContentController;
use App\Http\Controllers\Admin\RecipeController as AdminRecipeController;
use App\Http\Controllers\Admin\RecipeIngredientController;
use App\Http\Controllers\Admin\RecipeStepController as AdminRecipeStepController;
use App\Http\Controllers\Admin\RecipeVerificationController;
use App\Http\Controllers\Admin\ReleaseCandidateController;
use App\Http\Controllers\Admin\RestoreHistoryController;
use App\Http\Controllers\Admin\SeoOperationsController;
use App\Http\Controllers\Admin\SystemEventController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AffiliateController;
use App\Http\Controllers\AiKitchenController;
use App\Http\Controllers\Auth\SocialLoginController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\BlogCoverController;
use App\Http\Controllers\CategoryHubController;
use App\Http\Controllers\ContactRequestController;
use App\Http\Controllers\CookingSessionController;
use App\Http\Controllers\CuisineHubController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\InformationPageController;
use App\Http\Controllers\IngredientExplorerController;
use App\Http\Controllers\IngredientHubController;
use App\Http\Controllers\LegalInformationCenterController;
use App\Http\Controllers\LlmsTxtController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PantryController;
use App\Http\Controllers\PersonalizationController;
use App\Http\Controllers\PolicyPageController;
use App\Http\Controllers\RecipeCollectionController;
use App\Http\Controllers\RecipeCollectionLandingController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\RecipeRatingController;
use App\Http\Controllers\RssFeedController;
use App\Http\Controllers\SchedulerTriggerController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TipController;
use App\Http\Controllers\WeeklyMenuController;
use App\Models\BlogPost;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Monetization & Gelir Kanalları Route'ları
|--------------------------------------------------------------------------
*/
Route::get('/fiyatlandirma', [SubscriptionController::class, 'pricing'])->name('pricing');
Route::post('/abone-ol', [SubscriptionController::class, 'checkout'])->name('subscription.checkout')->middleware(['auth', 'throttle:payment-attempt']);
Route::post('/abonelik-iptal', [SubscriptionController::class, 'cancel'])->name('subscription.cancel')->middleware(['auth', 'throttle:payment-attempt']);
Route::post('/abonelik-yonet', [SubscriptionController::class, 'billingPortal'])->name('subscription.portal')->middleware(['auth', 'throttle:payment-attempt']);

Route::get('/magaza', [ShopController::class, 'index'])->name('shop.index');
Route::get('/magaza/{product:slug}', [ShopController::class, 'show'])->name('shop.show');
Route::post('/magaza/satin-al/{product:slug}', [ShopController::class, 'checkout'])->name('shop.checkout')->middleware('throttle:payment-attempt');
Route::get('/magaza/onay/{orderUuid}', [ShopController::class, 'success'])->middleware('signed')->name('shop.success');
Route::get('/magaza/indir/{downloadToken}', [ShopController::class, 'download'])->name('shop.download')->middleware('throttle:digital-download');

Route::get('/go/{slug}', [AffiliateController::class, 'redirect'])->name('affiliate.redirect');
Route::post('/destek-ol', [TipController::class, 'process'])->name('tip.process')->middleware('throttle:payment-attempt');

// Not: burada bir zamanlar `if (! Route::has('pricing'))` koşuluyla ikinci bir
// /fiyatlandirma rotası vardı. Route::has kayıt sırasında ad tablosunu henüz
// görmediği için koşul her zaman doğru çıkıyor, fallback yukarıdaki gerçek
// rotayı eziyordu: Stripe akışını taşıyan sayfaya hiç ulaşılamıyordu.
// Ödeme hazır değilken sayfayı görünür tutma işini zaten SubscriptionController
// yapıyor ($paymentsEnabled false iken düğmeler "Yakında" olur).

Route::get('/', [RecipeController::class, 'index'])->name('home');
Route::get('/haftalik-menu', [WeeklyMenuController::class, 'index'])->name('weekly-menu.index');
Route::post('/dil', LocaleController::class)->middleware('throttle:20,1')->name('locale.update');
// Sosyal giriş. Sağlayıcı yapılandırılmamışsa controller 404 döner ve düğme
// zaten basılmaz; anahtarsız bir düğme herkesi hata sayfasına götürür.
Route::get('/giris/{provider}/yonlendir', [SocialLoginController::class, 'redirect'])
    ->whereIn('provider', ['google', 'github'])
    ->middleware('throttle:20,1')
    ->name('social.redirect');
Route::get('/giris/{provider}/geri-donus', [SocialLoginController::class, 'callback'])
    ->whereIn('provider', ['google', 'github'])
    ->middleware('throttle:20,1')
    ->name('social.callback');

Route::get('/health', HealthController::class)->name('health');
Route::get('/tarifler', [RecipeController::class, 'index'])->name('recipes.index');
// Malzemeye göre tarif eşleştirme herkese açıktır; dolap entegrasyonu üye olanlara özel kalır.
Route::get('/ne-pisirebilirim', [IngredientExplorerController::class, 'index'])->name('ingredients.explore');

// Malzeme sayfaları: "Hellim ile ne yapılır?" Sayfa yalnız arkasında yeterli
// tarif varsa açılır; eşiğin altındaki malzeme 404 verir.
// Mutfak sayfalari: sitenin kendi arama kaydi "kibris mutfagi"ni 24 kez
// olctu ama indekslenebilir tek bir mutfak sayfasi yoktu.
// Kıbrıs 5'li varyant için kısa/anahtar kelime URL'leri — hepsi kanonik hub'a 301
// "Kıbrıs", "Kıbrıs Yemek", "Kıbrıs Yemek Tarif", "Yemek Kıbrıs", "Kıbrıs Yemekleri" tek sayfada toplanır
foreach (['kibris', 'kibris-yemek', 'kibris-yemekleri', 'kibris-yemek-tarifi', 'kibris-yemek-tarifleri', 'yemek-kibris'] as $kisa) {
    Route::redirect('/'.$kisa, '/mutfaklar/kibris-mutfagi', 301)->name('kibris.alias.'.$kisa);
    Route::redirect('/en/'.$kisa, '/en/mutfaklar/kibris-mutfagi', 301);
}
Route::get('/mutfaklar', [CuisineHubController::class, 'index'])->name('cuisines.index');
Route::get('/mutfaklar/{slug}', [CuisineHubController::class, 'show'])->name('cuisines.show');
Route::get('/kategoriler', [CategoryHubController::class, 'index'])->name('categories.index');
Route::get('/kategoriler/{slug}', [CategoryHubController::class, 'show'])->name('categories.show');
// /sure/30-dakika yayindaydi ama /sure 404 donuyordu: alt sayfasi olan ust
// sayfa eksikti. Diger uc hub index + show ciftiyle duruyor.
Route::get('/sure', [RecipeCollectionLandingController::class, 'sureler'])->name('collections.sure.index');
Route::get('/zorluk', [RecipeCollectionLandingController::class, 'zorluklar'])->name('collections.zorluk.index');
Route::get('/sure/{slug}', [RecipeCollectionLandingController::class, 'sure'])->name('collections.sure');
Route::get('/zorluk/{slug}', [RecipeCollectionLandingController::class, 'zorluk'])->name('collections.zorluk');
Route::get('/malzemeler', [IngredientHubController::class, 'index'])->name('ingredients.hub.index');
Route::get('/malzemeler/{slug}', [IngredientHubController::class, 'show'])->name('ingredients.hub.show');
Route::get('/tarifler/{recipe:slug}', [RecipeController::class, 'show'])
    ->name('recipes.show')
    // Tarif slug'ları rastgele bir sonek taşıyor ("adana-kebabi-MT5XA") ve tek
    // bir toplu içerik çalışması 678 tarifin 628'inin sonekini değiştirebiliyor.
    // Sonek değiştiğinde paylaşılmış ve indekslenmiş her adres 404 verir; asıl
    // ada göre tek bir karşılık varsa oraya kalıcı olarak yönlendirilir.
    ->missing(function (Request $request) {
        $slug = (string) $request->route('recipe');

        $redirect = \App\Models\SlugRedirect::query()
            ->where(function ($q) use ($slug) {
                $q->where('old_slug', $slug)
                    ->orWhere('old_slug', rawurldecode($slug));
            })
            ->first();

        if ($redirect !== null) {
            if ($redirect->model_type === BlogPost::class) {
                $blogTarget = BlogPost::query()->publiclyVisible()->find($redirect->model_id);
                if ($blogTarget !== null) {
                    return redirect()->route('blog.show', $blogTarget, 301);
                }
            } else {
                $target = Recipe::query()->where('status', 'published')->where('verification_status', 'verified')->find($redirect->model_id);
                if ($target !== null) {
                    return redirect()->route('recipes.show', $target, 301);
                }
            }
        }

        $govde = (string) preg_replace('/-[A-Za-z0-9]{5}$/', '', $slug);

        abort_if(! is_string($govde) || $govde === '' || $govde === $slug, 404);

        $adaylar = Recipe::query()
            ->where('status', 'published')
            ->where('verification_status', 'verified')
            ->where(fn ($q) => $q->where('slug', $govde)->orWhere('slug', 'like', $govde.'-_____'))
            ->limit(2)
            ->get();

        abort_unless($adaylar->count() === 1, 404);

        return redirect()->route('recipes.show', $adaylar->first(), 301);
    });
Route::post('/tarifler/{recipe:slug}/oyla', [RecipeRatingController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('recipes.rate');
Route::get('/embed/recipes/{recipe:slug}', [RecipeController::class, 'embed'])->name('recipes.embed');
Route::get('/embed/tarif/{recipe:slug}', [RecipeController::class, 'embed'])->name('recipes.embed.tr');
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/kategori/{category}', [BlogController::class, 'category'])->name('blog.category');
Route::get('/blog/etiket/{tag}', [BlogController::class, 'tag'])->name('blog.tag');
// Bir yazının slug'ı sonradan uzatılırsa (ör. sonuna ayırt edici bir ek gelirse)
// eski adres sessizce 404 olur ve indekslenmiş bağlantılar kırılır. Eski slug'la
// başlayan yayındaki yazıya kalıcı yönlendirme yapılır; bulunamazsa 404 sürer.
// Her yazının sosyal paylaşım görseli kendi başlığından üretilir; 216 yazının
// aynı varsayılan marka görselini paylaşması yerine.
Route::get('/blog/{post}/kapak.png', BlogCoverController::class)->name('blog.cover');
Route::get('/blog/{post}', [BlogController::class, 'show'])
    ->name('blog.show')
    ->missing(function (Request $request) {
        $slug = (string) $request->route('post');

        $redirect = \App\Models\SlugRedirect::query()
            ->where(function ($q) use ($slug) {
                $q->where('old_slug', $slug)
                    ->orWhere('old_slug', rawurldecode($slug));
            })
            ->first();

        if ($redirect !== null) {
            if ($redirect->model_type === Recipe::class) {
                $recipeTarget = Recipe::query()->where('status', 'published')->where('verification_status', 'verified')->find($redirect->model_id);
                if ($recipeTarget !== null) {
                    return redirect()->route('recipes.show', $recipeTarget, 301);
                }
            } else {
                $target = BlogPost::query()->publiclyVisible()->find($redirect->model_id);
                if ($target !== null) {
                    return redirect()->route('blog.show', $target, 301);
                }
            }
        }

        if (str_starts_with($slug, 'geo-2026-')) {
            $clean = substr($slug, 9);
            $cleanDecoded = rawurldecode($clean);
            $matches = BlogPost::query()->publiclyVisible()
                ->where(fn ($query) => $query
                    ->where('slug', $clean)
                    ->orWhere('slug', $cleanDecoded)
                    ->orWhere('slug', 'like', $clean.'-%')
                    ->orWhere('slug', 'like', $cleanDecoded.'-%'))
                ->limit(2)
                ->get();
            $match = $matches->count() === 1 ? $matches->first() : null;

            if ($match !== null) {
                return redirect()->route('blog.show', $match, 301);
            }

            abort(404);
        }

        $matches = BlogPost::query()->publiclyVisible()
            ->where('slug', 'like', $slug.'-%')
            ->orderByRaw('LENGTH(slug)')
            ->limit(50)
            ->get();
        $shortestLength = $matches->min(fn (BlogPost $post): int => strlen((string) $post->slug));
        $shortest = $matches->filter(fn (BlogPost $post): bool => strlen((string) $post->slug) === $shortestLength);
        $match = $shortest->count() === 1 ? $shortest->first() : null;

        abort_if($match === null, 404);

        return redirect()->route('blog.show', $match, 301);
    });
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/sitemaps/pages.xml', [SitemapController::class, 'pages'])->name('sitemap.pages');
Route::get('/sitemaps/recipes.xml', [SitemapController::class, 'recipes'])->name('sitemap.recipes');
Route::get('/sitemaps/blog.xml', [SitemapController::class, 'blog'])->name('sitemap.blog');
Route::get('/sitemaps/images.xml', [SitemapController::class, 'images'])->name('sitemap.images');
Route::get('/feed', [RssFeedController::class, 'index'])->name('feed');
Route::get('/feed.xml', [RssFeedController::class, 'index']);
Route::get('/rss.xml', [RssFeedController::class, 'index']);
Route::get('/en/feed', [RssFeedController::class, 'en'])->name('feed.en');
Route::get('/en/feed.xml', [RssFeedController::class, 'en']);
Route::get('/en/rss.xml', [RssFeedController::class, 'en']);

Route::get('/llms.txt', [LlmsTxtController::class, 'index'])->name('llms.txt');
Route::get('/llms-full.txt', [LlmsTxtController::class, 'full'])->name('llms.full');
Route::get('/en/llms.txt', [LlmsTxtController::class, 'en'])->name('llms.txt.en');
Route::get('/en/llms-full.txt', [LlmsTxtController::class, 'enFull'])->name('llms.full.en');
// Türkçe ve İngilizce korpusları elle yazıldı ve daha zengin; geri kalan her
// açık dil buradan otomatik korpus alır. Yeni bir dil açıldığında burada
// yapılacak bir iş yok - altyapının kendi kendine büyümesi bu demek.
Route::get('/{locale}/llms.txt', [LlmsTxtController::class, 'locale'])
    ->where('locale', '[a-z]{2}')
    ->name('llms.txt.locale');
Route::get('/{locale}/llms-full.txt', [LlmsTxtController::class, 'localeFull'])
    ->where('locale', '[a-z]{2}')
    ->name('llms.full.locale');

Route::get('/opensearch.xml', fn () => response()->view('opensearch', ['locale' => 'tr'])->header('Content-Type', 'application/opensearchdescription+xml; charset=UTF-8')->header('Cache-Control', 'public, max-age=86400'))->name('opensearch');
Route::get('/en/opensearch.xml', fn () => response()->view('opensearch', ['locale' => 'en'])->header('Content-Type', 'application/opensearchdescription+xml; charset=UTF-8')->header('Cache-Control', 'public, max-age=86400'))->name('opensearch.en');

Route::get('/manifest.webmanifest', fn () => response(file_get_contents(public_path('brand/site.webmanifest')), 200, ['Content-Type' => 'application/manifest+json; charset=UTF-8']));
Route::get('/en/manifest.webmanifest', fn () => response(file_get_contents(public_path('brand/site-en.webmanifest')), 200, ['Content-Type' => 'application/manifest+json; charset=UTF-8']))->name('manifest.en');
Route::get('/en/site.webmanifest', fn () => response(file_get_contents(public_path('brand/site-en.webmanifest')), 200, ['Content-Type' => 'application/manifest+json; charset=UTF-8']));
Route::get('/c032a1e8e8214371b658ef9c7e0b57e1.txt', fn () => response(config('services.indexnow.key', 'c032a1e8e8214371b658ef9c7e0b57e1'), 200, ['Content-Type' => 'text/plain; charset=UTF-8']));
Route::view('/robots.txt', 'robots')->name('robots');

// Zamanlayıcı tetikleme ucu. Hostinger cron'u 2 Ağustos 2026'dan beri
// çalışmıyor; bu uç dışarıdan bir zamanlayıcının uygulamayı dürtmesini
// sağlıyor. Token tanımlı değilse 404 döner, yani varsayılan kapalıdır.
Route::get('/_zamanlayici/{token}', SchedulerTriggerController::class)
    ->middleware('throttle:6,1')
    ->name('scheduler.trigger');
Route::get('/yasal-bilgilendirme', [LegalInformationCenterController::class, 'index'])->name('legal.center');
// İletişim formu yasal metnin içine gömülüydü; kendi sayfası oldu.
Route::view('/iletisim', 'contact.index')->name('contact.index');
Route::view('/private-chef', 'private-chef.index')->name('private-chef.index');
Route::redirect('/ozel-sef', '/private-chef', 301);
Route::view('/catering-cyprus', 'catering.index')->name('catering.index');
Route::view('/cooking-classes', 'cooking-classes.index')->name('cooking-classes.index');
Route::view('/meal-plans', 'meal-plans.index')->name('meal-plans.index');
Route::redirect('/catering', '/catering-cyprus', 301);
Route::redirect('/yemek-kursu', '/cooking-classes', 301);
Route::redirect('/diyet-plani', '/meal-plans', 301);
Route::redirect('/yemek-plani', '/meal-plans', 301);


Route::post('/yasal-bilgilendirme/iletisim', [ContactRequestController::class, 'store'])->middleware('throttle:contact-request')->name('legal.center.contact.store');
// Bu üç politika kendi sayfasında duruyor. Bölümler yasal merkezden
// taşındı, kopyalanmadı: aynı metin iki adreste dururken ikisi de
// yinelenen içerik sayılır.
Route::get('/gizlilik', PolicyPageController::class)->defaults('sayfa', 'gizlilik')->name('policy.gizlilik');
Route::get('/cerezler', PolicyPageController::class)->defaults('sayfa', 'cerezler')->name('policy.cerezler');
Route::get('/kullanim-kosullari', PolicyPageController::class)->defaults('sayfa', 'kullanim-kosullari')->name('policy.kullanim-kosullari');
Route::redirect('/gizlilik-politikasi', '/gizlilik', 301);
Route::redirect('/privacy-policy', '/gizlilik', 301);
Route::redirect('/cerez-politikasi', '/cerezler', 301);
Route::redirect('/cookie-policy', '/cerezler', 301);
Route::redirect('/kullanim-sartlari', '/kullanim-kosullari', 301);
Route::redirect('/terms-of-service', '/kullanim-kosullari', 301);
Route::redirect('/kaynak-politikasi', '/yasal-bilgilendirme#icerik-ve-kaynak-politikasi', 301);
Route::redirect('/kaynak-ve-veri-guven-politikasi', '/yasal-bilgilendirme#icerik-ve-kaynak-politikasi', 301);
Route::get('/{page}', [InformationPageController::class, 'show'])
    ->whereIn('page', ['hakkimizda', 'nasil-calisir', 'sss', 'editorial-ilkeler', 'tarif-dogrulama-politikasi', 'besin-degeri-aciklamasi', 'alerjen-uyarisi', 'gorsel-ve-medya-politikasi', 'gorsel-ve-kaynak-politikasi', 'icerik-duzeltme-politikasi', 'hata-bildir', 'iletisim'])
    ->name('information.show');

Route::view('dashboard', 'dashboard')->middleware(['auth', 'verified'])->name('dashboard');
Route::view('profile', 'profile')->middleware(['auth'])->name('profile');

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/sizin-icin', [PersonalizationController::class, 'index'])->name('personalization.index');
    Route::get('/sizin-icin/baslangic', [PersonalizationController::class, 'onboarding'])->name('personalization.onboarding');
    Route::post('/sizin-icin/baslangic', [PersonalizationController::class, 'completeOnboarding'])->name('personalization.onboarding.complete');
    Route::patch('/sizin-icin', [PersonalizationController::class, 'update'])->name('personalization.update');
    Route::post('/sizin-icin/ac', [PersonalizationController::class, 'enable'])->name('personalization.enable');
    Route::patch('/sizin-icin/gizlilik', [PersonalizationController::class, 'settings'])->name('personalization.settings');
    Route::post('/sizin-icin/export-talebi', [PersonalizationController::class, 'export'])->name('personalization.export');
    Route::post('/sizin-icin/menu', [PersonalizationController::class, 'mealPlan'])->name('personalization.meal-plan');
    Route::post('/sizin-icin/kapat', [PersonalizationController::class, 'disable'])->name('personalization.disable');
    Route::post('/sizin-icin/ogrenilenleri-sil', [PersonalizationController::class, 'clear'])->name('personalization.clear');
    Route::post('/sizin-icin/tarifler/{recipe:public_id}/geri-bildirim', [PersonalizationController::class, 'feedback'])->name('personalization.feedback');
    Route::get('/ai-mutfak', [AiKitchenController::class, 'index'])->name('ai-kitchen.index');
    Route::post('/ai-mutfak/asistan', [AiKitchenController::class, 'assistant'])->name('ai-kitchen.assistant');
    Route::post('/ai-mutfak/dolap', [AiKitchenController::class, 'pantry'])->name('ai-kitchen.pantry');
    Route::post('/ai-mutfak/artanlar', [AiKitchenController::class, 'leftovers'])->name('ai-kitchen.leftovers');
    Route::post('/ai-mutfak/menu', [AiKitchenController::class, 'mealPlan'])->name('ai-kitchen.meal-plan');
    Route::post('/ai-mutfak/menu/{plan:public_id}/alisveris', [AiKitchenController::class, 'shopping'])->name('ai-kitchen.shopping');
    Route::post('/ai-mutfak/tarifler/{recipe:public_id}/porsiyon', [AiKitchenController::class, 'portion'])->name('ai-kitchen.portion');
    Route::post('/ai-mutfak/tarifler/{recipe:public_id}/beslenme', [AiKitchenController::class, 'nutrition'])->name('ai-kitchen.nutrition');
    Route::get('/favoriler', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/tarifler/{recipe:slug}/favori', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
    Route::get('/koleksiyonlar', [RecipeCollectionController::class, 'index'])->name('collections.index');
    Route::post('/koleksiyonlar', [RecipeCollectionController::class, 'store'])->name('collections.store');
    Route::get('/koleksiyonlar/{recipeCollection:public_id}', [RecipeCollectionController::class, 'show'])->name('collections.show');
    Route::patch('/koleksiyonlar/{recipeCollection:public_id}', [RecipeCollectionController::class, 'update'])->name('collections.update');
    Route::delete('/koleksiyonlar/{recipeCollection:public_id}', [RecipeCollectionController::class, 'destroy'])->name('collections.destroy');
    Route::post('/koleksiyonlar/{recipeCollection:public_id}/tarifler/{recipe:slug}', [RecipeCollectionController::class, 'add'])->withoutScopedBindings()->name('collections.recipes.add');
    Route::delete('/koleksiyonlar/{recipeCollection:public_id}/tarifler/{recipe:slug}', [RecipeCollectionController::class, 'remove'])->name('collections.recipes.remove');
    Route::get('/dolabim', [PantryController::class, 'index'])->name('pantry.index');
    Route::post('/dolabim', [PantryController::class, 'store'])->name('pantry.store');
    Route::delete('/dolabim/{pantryItem}', [PantryController::class, 'destroy'])->name('pantry.destroy');
    Route::post('/tarifler/{recipe:slug}/pisir', [CookingSessionController::class, 'start'])->name('cooking-sessions.start');
    Route::get('/pisir/{cookingSession}', [CookingSessionController::class, 'show'])->name('cooking-sessions.show');
    Route::post('/pisir/{cookingSession}/adimlar/{step}/tamamla', [CookingSessionController::class, 'completeStep'])->name('cooking-sessions.steps.complete');
    Route::delete('/pisir/{cookingSession}/adimlar/{step}/tamamla', [CookingSessionController::class, 'undoStep'])->name('cooking-sessions.steps.undo');
    Route::post('/pisir/{cookingSession}/duraklat', [CookingSessionController::class, 'pause'])->name('cooking-sessions.pause');
    Route::post('/pisir/{cookingSession}/devam', [CookingSessionController::class, 'resume'])->name('cooking-sessions.resume');
    Route::post('/pisir/{cookingSession}/tamamla', [CookingSessionController::class, 'complete'])->name('cooking-sessions.complete');
    Route::post('/pisir/{cookingSession}/adimlar/{step}/zamanlayici', [CookingSessionController::class, 'createTimer'])->name('cooking-sessions.timers.create');
    Route::post('/pisir/{cookingSession}/zamanlayicilar/{timer}/duraklat', [CookingSessionController::class, 'pauseTimer'])->name('cooking-sessions.timers.pause');
    Route::post('/pisir/{cookingSession}/zamanlayicilar/{timer}/devam', [CookingSessionController::class, 'resumeTimer'])->name('cooking-sessions.timers.resume');
});

Route::prefix('yonetim')->name('admin.')->middleware(['auth', 'verified', 'can:access-media-operations'])->group(function (): void {
    Route::get('medya-operasyonlari', [MediaOperationsController::class, 'index'])->name('media-operations.index');
    Route::post('medya-operasyonlari/adaylar/{candidate:public_id}/sec', [MediaOperationsController::class, 'selectCandidate'])->name('media-operations.candidates.select');
    Route::post('medya-operasyonlari/adaylar/{candidate:public_id}/reddet', [MediaOperationsController::class, 'rejectCandidate'])->name('media-operations.candidates.reject');
});

Route::prefix('yonetim')->name('admin.')->middleware(['auth', 'verified', 'can:access-admin'])->group(function (): void {
    Route::get('entegrasyonlar', IntegrationCenterController::class)->name('integrations.index');
    Route::get('e-posta-operasyonlari', [MailOperationsController::class, 'index'])->name('mail-operations.index');
    Route::get('e-posta-operasyonlari/onizleme/{template}', [MailOperationsController::class, 'preview'])->name('mail-operations.preview');
    Route::get('iletisim-talepleri', [AdminContactRequestController::class, 'index'])->name('contact-requests.index');
    Route::get('iletisim-talepleri/{contactRequest:public_id}', [AdminContactRequestController::class, 'show'])->name('contact-requests.show');
    Route::put('iletisim-talepleri/{contactRequest:public_id}', [AdminContactRequestController::class, 'update'])->name('contact-requests.update');
    Route::post('iletisim-talepleri/{contactRequest:public_id}/notlar', [AdminContactRequestController::class, 'note'])->name('contact-requests.notes.store');
    Route::post('iletisim-talepleri/{contactRequest:public_id}/yanitla', [AdminContactRequestController::class, 'reply'])->name('contact-requests.reply');
    Route::post('iletisim-talepleri/{contactRequest:public_id}/anonimlestir', [AdminContactRequestController::class, 'anonymize'])->name('contact-requests.anonymize');
    Route::get('ai-operasyonlari', [AdminAiKitchenController::class, 'index'])->middleware('can:access-ai-operations')->name('ai-kitchen.index');
    Route::get('kisisellestirme-operasyonlari', [PersonalizationOperationsController::class, 'index'])->name('personalization.index');
    Route::get('/', AdminDashboardController::class)->name('dashboard');
    Route::get('urun-deneyimi', ProductExperienceController::class)->name('product-experience.index');
    Route::get('release-candidate', ReleaseCandidateController::class)->name('release-candidate.index');
    Route::get('kullanicilar', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('kullanicilar/{user:public_id}', [AdminUserController::class, 'show'])->name('users.show');
    Route::post('kullanicilar/{user:public_id}/askiya-al', [AdminUserController::class, 'suspend'])->name('users.suspend');
    Route::post('kullanicilar/{user:public_id}/etkinlestir', [AdminUserController::class, 'reactivate'])->name('users.reactivate');
    Route::post('kullanicilar/{user:public_id}/oturumlari-kapat', [AdminUserController::class, 'revokeSessions'])->name('users.revoke-sessions');
    Route::post('sistem-durumu/queue-yenile', [AdminDashboardController::class, 'refreshQueue'])->name('system-status.refresh-queue');
    Route::post('sistem-durumu/scheduler-yenile', [AdminDashboardController::class, 'refreshScheduler'])->name('system-status.refresh-scheduler');
    Route::post('sistem-durumu/cache-yenile', [AdminDashboardController::class, 'refreshCache'])->name('system-status.refresh-cache');
    Route::get('basarisiz-isler', [FailedJobController::class, 'index'])->name('failed-jobs.index');
    Route::get('sistem-olaylari', [SystemEventController::class, 'index'])->name('system-events.index');
    Route::get('sistem-olaylari/{systemEvent}', [SystemEventController::class, 'show'])->name('system-events.show');
    Route::post('sistem-olaylari/{systemEvent}/coz', [SystemEventController::class, 'resolve'])->name('system-events.resolve');
    Route::post('sistem-olaylari/{systemEvent}/yeniden-ac', [SystemEventController::class, 'reopen'])->name('system-events.reopen');
    Route::get('yedekler', [BackupController::class, 'index'])->name('backups.index');
    Route::post('yedekler', [BackupController::class, 'create'])->name('backups.create');
    Route::post('yedekler/temizle', [BackupController::class, 'cleanup'])->name('backups.cleanup');
    Route::post('yedekler/{id}/verify', [BackupController::class, 'verify'])->name('backups.verify');
    Route::get('yedekler/{id}/indir', [BackupController::class, 'download'])->name('backups.download');
    Route::get('restore-gecmisi', [RestoreHistoryController::class, 'index'])->name('restore-history.index');
    Route::get('restore-gecmisi/{restoreHistory}', [RestoreHistoryController::class, 'show'])->name('restore-history.show');
    Route::get('basarisiz-isler/{uuid}', [FailedJobController::class, 'show'])->name('failed-jobs.show');
    Route::post('basarisiz-isler/{uuid}/retry', [FailedJobController::class, 'retry'])->name('failed-jobs.retry');
    Route::post('basarisiz-isler/toplu-yeniden-dene', [FailedJobController::class, 'retryMany'])->name('failed-jobs.retry-many');
    Route::delete('basarisiz-isler/{uuid}', [FailedJobController::class, 'forget'])->name('failed-jobs.forget');
    Route::get('medya', [AdminMediaController::class, 'index'])->name('media.index');
    Route::resource('blog', AdminBlogPostController::class)->parameters(['blog' => 'post'])->only(['index', 'create', 'store', 'edit', 'update']);
    Route::post('blog/uygun-olanlari-yayinla', [AdminBlogPostController::class, 'publishReady'])->name('blog.publish-ready');
    Route::get('blog/{post}/inceleme', [AdminBlogPostController::class, 'review'])->name('blog.review');
    Route::post('blog/{post}/yayinla', [AdminBlogPostController::class, 'publish'])->name('blog.publish');
    Route::post('blog/{post}/arsivle', [AdminBlogPostController::class, 'archive'])->name('blog.archive');
    Route::get('seo', [SeoOperationsController::class, 'index'])->name('seo.index');
    Route::get('seo/buyume', [SeoOperationsController::class, 'growth'])->name('seo.growth');
    Route::post('seo/buyume/yenile', [SeoOperationsController::class, 'refreshGrowth'])->name('seo.growth.refresh');
    Route::post('seo/buyume/csv', [SeoOperationsController::class, 'importBacklinks'])->name('seo.growth.import');
    Route::post('seo/buyume/{candidate:public_id}/incele', [SeoOperationsController::class, 'reviewBacklink'])->name('seo.growth.review');
    Route::post('seo/firsatlar/{opportunity:public_id}/incele', [SeoOperationsController::class, 'reviewOpportunity'])->name('seo.opportunities.review');
    Route::get('seo/sorunlar', [SeoOperationsController::class, 'issues'])->name('seo.issues');
    Route::get('seo/firsatlar', [SeoOperationsController::class, 'opportunities'])->name('seo.opportunities');
    Route::post('seo/denetle', [SeoOperationsController::class, 'runAudit'])->name('seo.audit');
    Route::post('seo/firsatlari-yenile', [SeoOperationsController::class, 'refreshOpportunities'])->name('seo.opportunities.refresh');
    Route::post('seo/sorunlar/{issue}/coz', [SeoOperationsController::class, 'resolve'])->name('seo.issues.resolve');
    Route::post('seo/sorunlar/{issue}/yeniden-ac', [SeoOperationsController::class, 'reopen'])->name('seo.issues.reopen');
    Route::post('seo/firsatlar/{opportunity}/durum', [SeoOperationsController::class, 'updateOpportunityStatus'])->name('seo.opportunities.update-status');
    Route::get('seo/ai-notlar', [SeoOperationsController::class, 'aiMentions'])->name('seo.ai-mentions');
    Route::get('seo/citation-boslarlari', [SeoOperationsController::class, 'citationGaps'])->name('seo.citation-gaps');
    Route::get('seo/llm-yapi', [SeoOperationsController::class, 'llmStructure'])->name('seo.llm-structure');
    Route::get('seo/haftalik-rapor', [SeoOperationsController::class, 'weeklyReport'])->name('seo.weekly-report');
    Route::get('gorsel-talepleri', [ImageRequestController::class, 'index'])->name('image-requests.index');
    Route::get('ai-gorsel-studyosu', [AiImageStudioController::class, 'index'])->name('ai-image-studio.index');
    Route::get('ai-gorsel-studyosu/{generation}', [AiImageStudioController::class, 'show'])->name('ai-image-studio.show');
    Route::get('ai-gorsel-studyosu/{generation}/onizleme', [AiImageStudioController::class, 'preview'])->name('ai-image-studio.preview');
    Route::post('ai-gorsel-studyosu/{generation}/onayla', [AiImageStudioController::class, 'approve'])->name('ai-image-studio.approve');
    Route::post('ai-gorsel-studyosu/{generation}/reddet', [AiImageStudioController::class, 'reject'])->name('ai-image-studio.reject');
    Route::post('ai-gorsel-studyosu/pexels/ice-aktar', [AiImageStudioController::class, 'importPexels'])->name('ai-image-studio.pexels.import');
    Route::post('ai-gorsel-studyosu/pexels/{media:public_id}/onayla', [AiImageStudioController::class, 'approvePexels'])->name('ai-image-studio.pexels.approve');
    Route::post('ai-gorsel-studyosu/pexels/{media:public_id}/reddet', [AiImageStudioController::class, 'rejectPexels'])->name('ai-image-studio.pexels.reject');
    Route::post('ai-gorsel-studyosu/pexels-aday/{candidate:public_id}/sec', [AiImageStudioController::class, 'selectCandidate'])->name('ai-image-studio.pexels-candidates.select');
    Route::post('ai-gorsel-studyosu/pexels-aday/{candidate:public_id}/reddet', [AiImageStudioController::class, 'rejectCandidate'])->name('ai-image-studio.pexels-candidates.reject');
    Route::post('ai-gorsel-studyosu/pexels-adaylar/{recipe:public_id}/tumunu-reddet', [AiImageStudioController::class, 'rejectRecipeCandidates'])->name('ai-image-studio.pexels-candidates.reject-all');
    Route::post('ai-gorsel-studyosu/pexels-aday/{candidate:public_id}/ice-aktar', [AiImageStudioController::class, 'importSelectedCandidate'])->name('ai-image-studio.pexels-candidates.import');
    Route::post('ai-gorsel-studyosu/pexels-fallback/{recipe:public_id}', [AiImageStudioController::class, 'keepPexelsFallback'])->name('ai-image-studio.pexels-candidates.keep-fallback');
    Route::post('ai-gorsel-studyosu/unsplash/{recipe:public_id}/ara', [AiImageStudioController::class, 'searchUnsplash'])->name('ai-image-studio.unsplash.search');
    Route::post('ai-gorsel-studyosu/unsplash-aday/{candidate:public_id}/sec', [AiImageStudioController::class, 'selectUnsplashCandidate'])->name('ai-image-studio.unsplash-candidates.select');
    Route::post('ai-gorsel-studyosu/unsplash-aday/{candidate:public_id}/reddet', [AiImageStudioController::class, 'rejectUnsplashCandidate'])->name('ai-image-studio.unsplash-candidates.reject');
    Route::post('ai-gorsel-studyosu/unsplash-aday/{candidate:public_id}/onayla', [AiImageStudioController::class, 'approveUnsplashCandidate'])->name('ai-image-studio.unsplash-candidates.approve');
    Route::get('medya/yukle', [AdminMediaController::class, 'create'])->name('media.create');
    Route::post('medya', [AdminMediaController::class, 'store'])->name('media.store');
    // İçe aktarma ağır bir iş: aynı anda iki kez başlatmanın anlamı yok.
    Route::post('medya/ice-aktar', [AdminMediaController::class, 'intake'])
        ->middleware('throttle:6,1')
        ->name('media.intake');
    Route::post('medya/orphan-tara', [AdminMediaController::class, 'scanOrphans'])->name('media.scan-orphans');
    Route::post('medya/gecici-temizle', [AdminMediaController::class, 'cleanupTemporary'])->name('media.cleanup-temporary');
    Route::get('medya/{publicId}', [AdminMediaController::class, 'show'])->name('media.show');
    Route::get('medya/{publicId}/indir', [AdminMediaController::class, 'downloadPrivate'])->name('media.download-private');
    Route::post('medya/{publicId}/thumbnail', [AdminMediaController::class, 'regenerate'])->name('media.regenerate');
    Route::post('medya/toplu-onayla', [AdminMediaController::class, 'approveMany'])->name('media.approve-many');
    Route::post('medya/{publicId}/onayla', [AdminMediaController::class, 'approve'])->name('media.approve');
    Route::delete('medya/{publicId}', [AdminMediaController::class, 'destroy'])->name('media.destroy');
    Route::get('veri-kalitesi', [DataQualityController::class, 'index'])->name('data-quality.index');
    Route::get('veri-kalitesi/{issue}', [DataQualityController::class, 'show'])->name('data-quality.show');
    Route::post('veri-kalitesi/tara', [DataQualityController::class, 'scan'])->name('data-quality.scan');
    Route::post('veri-kalitesi/toplu-islem', [DataQualityController::class, 'bulk'])->name('data-quality.bulk');
    Route::post('veri-kalitesi/{issue}/coz', [DataQualityController::class, 'resolve'])->name('data-quality.resolve');
    Route::post('veri-kalitesi/{issue}/yeniden-ac', [DataQualityController::class, 'reopen'])->name('data-quality.reopen');
    Route::get('beslenme', [AdminNutritionController::class, 'index'])->name('nutrition.index');
    Route::get('beslenme/snapshots/{snapshot}', [AdminNutritionController::class, 'snapshot'])->name('nutrition.snapshots.show');
    Route::get('beslenme/gecmis', [AdminNutritionController::class, 'history'])->name('nutrition.history');
    Route::post('beslenme/nutrients', [AdminNutritionController::class, 'saveNutrient'])->name('nutrition.nutrients.store');
    Route::put('beslenme/nutrients/{nutrient}', [AdminNutritionController::class, 'saveNutrient'])->name('nutrition.nutrients.update');
    Route::post('beslenme/allergens', [AdminNutritionController::class, 'saveAllergen'])->name('nutrition.allergens.store');
    Route::put('beslenme/allergens/{allergen}', [AdminNutritionController::class, 'saveAllergen'])->name('nutrition.allergens.update');
    Route::post('beslenme/dietary-tags', [AdminNutritionController::class, 'saveDietaryTag'])->name('nutrition.dietary-tags.store');
    Route::put('beslenme/dietary-tags/{tag}', [AdminNutritionController::class, 'saveDietaryTag'])->name('nutrition.dietary-tags.update');
    Route::post('beslenme/malzemeler/{ingredient}/facts', [AdminNutritionController::class, 'saveFact'])->name('nutrition.facts.store');
    Route::post('beslenme/ingredient-allergens', [AdminNutritionController::class, 'saveRelation'])->name('nutrition.relations.store');
    Route::post('beslenme/ingredient-allergens/{relation}/toggle', [AdminNutritionController::class, 'toggleRelation'])->name('nutrition.relations.toggle');
    Route::post('beslenme/recipe-allergen-overrides', [AdminNutritionController::class, 'saveOverride'])->name('nutrition.overrides.store');
    Route::post('beslenme/recipe-allergen-overrides/{override}/toggle', [AdminNutritionController::class, 'toggleOverride'])->name('nutrition.overrides.toggle');
    Route::post('beslenme/settings', [AdminNutritionController::class, 'saveSetting'])->name('nutrition.settings.store');
    Route::post('beslenme/tarifler/{recipe}/invalidate', [AdminNutritionController::class, 'invalidate'])->name('nutrition.snapshots.invalidate');
    Route::post('beslenme/tarifler/{recipe}/recalculate', [AdminNutritionController::class, 'recalculate'])->name('nutrition.snapshots.recalculate');
    Route::resource('tarifler', AdminRecipeController::class)->names('recipes')->parameters(['tarifler' => 'recipe'])->except(['show']);
    Route::get('icerik-dogrulama', [RecipeVerificationController::class, 'index'])->name('recipe-verification.index');
    Route::get('icerik-dogrulama/{recipe:public_id}', [RecipeVerificationController::class, 'show'])->name('recipe-verification.show');
    Route::post('icerik-dogrulama/{recipe:public_id}/incele', [RecipeVerificationController::class, 'approve'])->name('recipe-verification.approve');
    Route::post('icerik-dogrulama/{recipe:public_id}/duzeltme-iste', [RecipeVerificationController::class, 'requestChanges'])->name('recipe-verification.request-changes');
    Route::post('icerik-dogrulama/{recipe:public_id}/reddet', [RecipeVerificationController::class, 'reject'])->name('recipe-verification.reject');
    Route::post('icerik-dogrulama/{recipe:public_id}/yayinla', [RecipeVerificationController::class, 'publish'])->name('recipe-verification.publish');
    Route::post('icerik-dogrulama/{recipe:public_id}/yayindan-kaldir', [RecipeVerificationController::class, 'unpublish'])->name('recipe-verification.unpublish');
    Route::get('tarifler/public/{publicId}/malzemeler', [RecipeIngredientController::class, 'indexPublic'])->name('recipes.ingredients.index-public');
    Route::get('tarifler/{recipe}/malzemeler', [RecipeIngredientController::class, 'index'])->name('recipes.ingredients.index');
    Route::post('tarifler/{recipe}/malzemeler', [RecipeIngredientController::class, 'store'])->name('recipes.ingredients.store');
    Route::put('tarifler/{recipe}/malzemeler/{line}', [RecipeIngredientController::class, 'update'])->name('recipes.ingredients.update');
    Route::delete('tarifler/{recipe}/malzemeler/{line}', [RecipeIngredientController::class, 'destroy'])->name('recipes.ingredients.destroy');
    Route::post('tarifler/{recipe}/malzemeler/sirala', [RecipeIngredientController::class, 'reorder'])->name('recipes.ingredients.reorder');
    Route::post('tarifler/{recipe}/malzemeler/gruplar', [RecipeIngredientController::class, 'storeGroup'])->name('recipes.ingredients.groups.store');
    Route::put('tarifler/{recipe}/malzemeler/gruplar/{group}', [RecipeIngredientController::class, 'updateGroup'])->name('recipes.ingredients.groups.update');
    Route::delete('tarifler/{recipe}/malzemeler/gruplar/{group}', [RecipeIngredientController::class, 'destroyGroup'])->name('recipes.ingredients.groups.destroy');
    Route::post('tarifler/{recipe}/malzemeler/gruplar/{group}/sirala', [RecipeIngredientController::class, 'reorderGroup'])->name('recipes.ingredients.groups.reorder');
    Route::post('tarifler/{recipe}/malzemeler/{line}/tasima', [RecipeIngredientController::class, 'move'])->name('recipes.ingredients.move');
    Route::get('tarifler/public/{publicId}/duzenle', [AdminRecipeController::class, 'editPublic'])->name('recipes.edit-public');
    Route::get('malzemeler', [AdminIngredientController::class, 'index'])->name('ingredients.index');
    Route::get('olculer', [MeasurementController::class, 'units'])->name('measurements.units');
    Route::post('olculer', [MeasurementController::class, 'storeUnit'])->name('measurements.units.store');
    Route::put('olculer/{unit}', [MeasurementController::class, 'updateUnit'])->name('measurements.units.update');
    Route::post('olculer/{unit}/toggle', [MeasurementController::class, 'toggleUnit'])->name('measurements.units.toggle');
    Route::get('malzeme-donusumleri', [MeasurementController::class, 'conversions'])->name('measurements.conversions');
    Route::post('malzeme-donusumleri', [MeasurementController::class, 'store'])->name('measurements.conversions.store');
    Route::put('malzeme-donusumleri/{conversion}', [MeasurementController::class, 'update'])->name('measurements.conversions.update');
    Route::post('malzeme-donusumleri/{conversion}/toggle', [MeasurementController::class, 'toggle'])->name('measurements.conversions.toggle');
    Route::get('malzemeler/public/{publicId}/duzenle', [AdminIngredientController::class, 'editPublic'])->name('ingredients.edit-public');
    Route::get('malzemeler/{ingredient}/duzenle', [AdminIngredientController::class, 'edit'])->name('ingredients.edit');
    Route::put('malzemeler/{ingredient}', [AdminIngredientController::class, 'update'])->name('ingredients.update');
    Route::post('malzemeler/{ingredient}/aliaslar', [AdminIngredientController::class, 'storeAlias'])->name('ingredients.aliases.store');
    Route::post('malzemeler/{ingredient}/alternatifler', [AdminIngredientController::class, 'storeSubstitution'])->name('ingredients.substitutions.store');
    Route::get('tarifler/{recipe}/adimlar', [AdminRecipeStepController::class, 'index'])->name('recipes.steps.index');
    Route::get('tarifler/{recipe}/adimlar/ekle', [AdminRecipeStepController::class, 'create'])->name('recipes.steps.create');
    Route::post('tarifler/{recipe}/adimlar', [AdminRecipeStepController::class, 'store'])->name('recipes.steps.store');
    Route::get('tarifler/{recipe}/adimlar/{step}/duzenle', [AdminRecipeStepController::class, 'edit'])->name('recipes.steps.edit');
    Route::put('tarifler/{recipe}/adimlar/{step}', [AdminRecipeStepController::class, 'update'])->name('recipes.steps.update');
    Route::delete('tarifler/{recipe}/adimlar/{step}', [AdminRecipeStepController::class, 'destroy'])->name('recipes.steps.destroy');
    Route::post('tarifler/{recipe}/adimlar/sirala', [AdminRecipeStepController::class, 'reorder'])->name('recipes.steps.reorder');
});

Route::prefix('yonetim')->name('admin.')->middleware(['auth', 'verified', 'can:access-content-factory'])->group(function (): void {
    Route::get('production-icerik', [ProductionContentController::class, 'index'])->name('production-content.index');
    Route::post('production-icerik/tara', [ProductionContentController::class, 'rescan'])->name('production-content.rescan');
    Route::post('production-icerik/{profile}/asama', [ProductionContentController::class, 'advance'])->name('production-content.advance');
    Route::post('production-icerik/{profile}/insan-onayi', [ProductionContentController::class, 'approve'])->name('production-content.approve');
    Route::post('production-icerik/{profile}/yayinla', [ProductionContentController::class, 'publish'])->name('production-content.publish');
    Route::get('icerik-fabrikasi', [ContentFactoryController::class, 'index'])->name('content-factory.index');
    Route::get('icerik-fabrikasi/review/{review}', [ContentFactoryController::class, 'show'])->name('content-factory.reviews.show');
    Route::post('icerik-fabrikasi/review/{review}/onayla', [ContentFactoryController::class, 'approve'])->name('content-factory.reviews.approve');
    Route::post('icerik-fabrikasi/review/{review}/reddet', [ContentFactoryController::class, 'reject'])->name('content-factory.reviews.reject');
    Route::post('icerik-fabrikasi/review/{review}/duzeltme-iste', [ContentFactoryController::class, 'requestRevision'])->name('content-factory.reviews.request-revision');
    Route::get('icerik-fabrikasi/fact-check', [ContentFactoryController::class, 'factChecks'])->name('content-factory.fact-checks.index');
    Route::get('icerik-fabrikasi/fact-check/{issue}', [ContentFactoryController::class, 'factCheckShow'])->name('content-factory.fact-checks.show');
    Route::post('icerik-fabrikasi/fact-check/{issue}/karar', [ContentFactoryController::class, 'factCheckAction'])->name('content-factory.fact-checks.action');
    Route::post('icerik-fabrikasi/revision/{revision}/islem', [ContentFactoryController::class, 'revisionAction'])->name('content-factory.revisions.action');
    Route::get('icerik-fabrikasi/duplicate-groups', [ContentFactoryController::class, 'duplicateGroups'])->name('content-factory.duplicate-groups.index');
    Route::get('icerik-fabrikasi/duplicate-groups/{groupKey}', [ContentFactoryController::class, 'duplicateGroup'])->name('content-factory.duplicate-groups.show');
    Route::post('icerik-fabrikasi/duplicate-groups/{groupKey}/dry-run', [ContentFactoryController::class, 'duplicateDryRun'])->name('content-factory.duplicate-groups.dry-run');
    Route::post('icerik-fabrikasi/duplicate-runs/{run}/propose', [ContentFactoryController::class, 'duplicatePropose'])->name('content-factory.duplicate-groups.propose');
    Route::get('icerik-fabrikasi/readiness', [ContentFactoryController::class, 'readiness'])->name('content-factory.readiness.index');
    Route::get('icerik-fabrikasi/readiness/{batch}', [ContentFactoryController::class, 'readinessShow'])->name('content-factory.readiness.show');
    Route::post('icerik-fabrikasi/readiness/rescan', [ContentFactoryController::class, 'readinessRescan'])->name('content-factory.readiness.rescan');

    Route::get('editorial-operasyonlar', [EditorialOperationsController::class, 'index'])->name('editorial-operations.index');
    Route::post('editorial-operasyonlar/yenile', [EditorialOperationsController::class, 'refresh'])->name('editorial-operations.refresh');
    Route::get('editorial-operasyonlar/batchler', [EditorialOperationsController::class, 'batches'])->name('editorial-operations.batches.index');
    Route::get('editorial-operasyonlar/batchler/{batch}', [EditorialOperationsController::class, 'batch'])->name('editorial-operations.batches.show');
    Route::post('editorial-operasyonlar/batchler/{batch}/ata', [EditorialOperationsController::class, 'assignBatch'])->name('editorial-operations.batches.assign');
    Route::get('editorial-operasyonlar/kaynak-kuyrugu', [EditorialOperationsController::class, 'sources'])->name('editorial-operations.sources.index');
    Route::get('editorial-operasyonlar/kaynak-kuyrugu/{item}', [EditorialOperationsController::class, 'source'])->name('editorial-operations.sources.show');
    Route::put('editorial-operasyonlar/kaynak-kuyrugu/{item}', [EditorialOperationsController::class, 'saveSource'])->name('editorial-operations.sources.update');
    Route::post('editorial-operasyonlar/kaynak-kuyrugu/{item}/ata', [EditorialOperationsController::class, 'assignSource'])->name('editorial-operations.sources.assign');
    Route::get('editorial-operasyonlar/review-paketleri', [EditorialOperationsController::class, 'packs'])->name('editorial-operations.packs.index');
    Route::get('editorial-operasyonlar/review-paketleri/{pack}', [EditorialOperationsController::class, 'pack'])->name('editorial-operations.packs.show');
    Route::post('editorial-operasyonlar/review-paketleri/{pack}/bolum', [EditorialOperationsController::class, 'section'])->name('editorial-operations.packs.section');
    Route::post('editorial-operasyonlar/review-paketleri/{pack}/ata', [EditorialOperationsController::class, 'assignPack'])->name('editorial-operations.packs.assign');
    Route::get('editorial-operasyonlar/final-onay', [EditorialOperationsController::class, 'finalApprovals'])->name('editorial-operations.final-approvals');
    Route::get('editorial-operasyonlar/medya', [EditorialOperationsController::class, 'media'])->name('editorial-operations.media');
    Route::get('editorial-operasyonlar/reviewer-is-yuku', [EditorialOperationsController::class, 'workload'])->name('editorial-operations.workload');
});

require __DIR__.'/auth.php';

// Yönetim, üyelik ve kullanıcı dostu URL uyumluluk yönlendirmeleri
Route::redirect('/admin', '/yonetim', 301);
Route::redirect('/admin/{path}', '/yonetim/{path}', 301)->where('path', '.*');
Route::redirect('/giris', '/login', 301);
Route::redirect('/oturum-ac', '/login', 301);
Route::redirect('/kayit', '/register', 301);
Route::redirect('/uye-ol', '/register', 301);
Route::redirect('/panel', '/dashboard', 301);
Route::redirect('/profil', '/profile', 301);
Route::redirect('/hesabim', '/profile', 301);
Route::redirect('/yasal', '/yasal-bilgilendirme', 301);
Route::redirect('/contact', '/iletisim', 301);
Route::redirect('/recipes', '/tarifler', 301);
Route::redirect('/about', '/hakkimizda', 301);
Route::redirect('/privacy', '/gizlilik', 301);
Route::redirect('/terms', '/kullanim-kosullari', 301);
Route::redirect('/cookies', '/cerezler', 301);
Route::redirect('/faq', '/sss', 301);
Route::redirect('/pantry', '/dolabim', 301);
Route::redirect('/dolap', '/dolabim', 301);
Route::redirect('/favorites', '/favoriler', 301);
Route::redirect('/koleksiyonlarim', '/koleksiyonlar', 301);
Route::redirect('/pricing', '/fiyatlandirma', 301);
Route::redirect('/shop', '/magaza', 301);
