@php($siteFeatures = app(\App\Services\SiteFeatureService::class))
<footer class="hc-footer mt-20">
            {{-- Beş dengeli sütun: marka (+bülten) ve dört bağlantı kümesi.
                 Eskiden altı blok vardı; altıncı (Bülten) ızgaraya sığmayıp
                 alta sarkıyor, sayfa dağınık görünüyordu. Bağlantı silinmedi:
                 iç-bağlantı denetimi (SEO-007) yetim sayfa istemiyor; yalnız
                 yinelenen "Kıbrıs Yemek" çifti teke indi. --}}
            <div class="hc-container grid gap-10 py-14 sm:grid-cols-2 lg:grid-cols-[1.4fr_1fr_1fr_1fr_1fr]">
                <div>
                    <x-brand-logo variant="dark" />
                    <p class="hc-footer-muted mt-5 max-w-sm text-sm leading-7">{{ __('footer.about_short') }}</p>
                    {{-- İç not ("Sosyal kanallar ... yayına alındığında") ziyaretçiye
                         basılıyordu; formu olmayan "Bülten" başlığı da abonelik
                         vaat ediyordu. Blok ne yaptığını söyler: ücretsiz üyelik. --}}
                    <p class="mt-6 text-sm font-extrabold text-orange-300">{{ __('footer.signup_title') }}</p>
                    <p class="hc-footer-muted mt-2 max-w-sm text-sm leading-6">{{ __('footer.signup_text') }}</p>
                    <a href="{{ route('register') }}" class="hc-btn hc-btn-primary mt-4">{{ __('footer.free_signup') }}</a>
                </div>
                <div><p class="text-sm font-extrabold text-orange-300">{{ __('footer.explore') }}</p><div class="hc-footer-muted mt-4 grid gap-2.5 text-sm [&>a]:inline-flex [&>a]:min-h-[24px] [&>a]:items-center"><a class="hover:text-orange-300" href="{{ route('recipes.index') }}">{{ __('nav.recipes') }}</a>@if($siteFeatures->enabled('explore'))<a class="hover:text-orange-300" href="{{ route('ingredients.explore') }}">{{ __('nav.explore') }}</a>@endif<a class="hover:text-orange-300" href="{{ route('blog.index') }}">{{ __('nav.blog') }}</a><a class="hover:text-orange-300" href="{{ route('weekly-menu.index') }}">{{ __('nav.weekly_menu') }}</a>@if($siteFeatures->enabled('shop'))<a class="hover:text-orange-300" href="{{ route('shop.index') }}">{{ __('nav.shop') }} (E-Kitap)</a>@endif<a class="hover:text-orange-300" href="{{ route('cuisines.index') }}">{{ __('footer.cuisines') }}</a><a class="hover:text-orange-300" href="{{ route('categories.index') }}">{{ __('footer.categories') }}</a><a class="hover:text-orange-300" href="{{ route('ingredients.hub.index') }}">{{ __('footer.ingredients') }}</a><a class="hover:text-orange-300" href="{{ route('collections.sure.index') }}">{{ __('footer.by_duration') }}</a><a class="hover:text-orange-300" href="{{ route('collections.zorluk.index') }}">{{ __('footer.by_difficulty') }}</a>@auth @if($siteFeatures->enabled('member_kitchen'))<a class="hover:text-orange-300" href="{{ route('pantry.index') }}">{{ __('nav.pantry') }}</a>@endif @endauth</div></div>
                {{-- On bir baglanti tek dar sutunun icinde iki sutuna sikistirilmisti;
                     "Sik sorulan / sorular" ve "Yasal / bilgilendirme" diye
                     kiriliyordu. Iki ayri baslik, her biri tek sutun. --}}
                <div>
                    <p class="text-sm font-extrabold text-orange-300">{{ __('footer.platform') }}</p>
                    <div class="hc-footer-muted mt-4 grid gap-3 text-sm [&>a]:inline-flex [&>a]:min-h-[24px] [&>a]:items-center">
                        <a class="hover:text-orange-300" href="{{ route('information.show', 'hakkimizda') }}">{{ __('footer.about_us') }}</a>
                        <a class="hover:text-orange-300" href="{{ route('information.show', 'nasil-calisir') }}">{{ __('footer.how_it_works') }}</a>
                        <a class="hover:text-orange-300" href="{{ route('information.show', 'sss') }}">{{ __('footer.faq') }}</a>
                        <a class="hover:text-orange-300" href="{{ route('information.show', 'tarif-dogrulama-politikasi') }}">{{ __('footer.recipe_verification') }}</a>
                        <a class="hover:text-orange-300" href="{{ route('information.show', 'editorial-ilkeler') }}">{{ __('footer.editorial_principles') }}</a>
                        <a class="hover:text-orange-300" href="{{ route('information.show', 'hata-bildir') }}">{{ __('footer.report_issue') }}</a>
                        <a class="hover:text-orange-300" href="{{ route('contact.index') }}">{{ __('footer.contact') }}</a>
                    </div>
                </div>

                <div>
                    <p class="text-sm font-extrabold text-orange-300">{{ __('footer.legal') }}</p>
                    <div class="hc-footer-muted mt-4 grid gap-3 text-sm [&>a]:inline-flex [&>a]:min-h-[24px] [&>a]:items-center">
                        <a class="hover:text-orange-300" href="{{ route('policy.gizlilik') }}">{{ __('footer.privacy') }}</a>
                        <a class="hover:text-orange-300" href="{{ route('policy.kullanim-kosullari') }}">{{ __('footer.terms') }}</a>
                        <a class="hover:text-orange-300" href="{{ route('policy.cerezler') }}">{{ __('footer.cookies') }}</a>
                        <a class="hover:text-orange-300" href="{{ route('legal.center') }}#gorsel-ve-yapay-zeka-politikasi">{{ __('footer.image_policy') }}</a>
                        <a class="hover:text-orange-300" href="{{ route('legal.center') }}#icerik-ve-kaynak-politikasi">{{ __('footer.source_policy') }}</a>
                        <a class="hover:text-orange-300" href="{{ route('information.show', 'besin-degeri-aciklamasi') }}">{{ __('footer.nutrition_disclaimer') }}</a>
                        <a class="hover:text-orange-300" href="{{ route('information.show', 'alerjen-uyarisi') }}">{{ __('footer.allergen_warning') }}</a>
                        <a class="hover:text-orange-300" href="{{ route('information.show', 'gorsel-ve-medya-politikasi') }}">{{ __('footer.media_policy') }}</a>
                        <a class="hover:text-orange-300" href="{{ route('information.show', 'icerik-duzeltme-politikasi') }}">{{ __('footer.corrections_policy') }}</a>
                    </div>
                </div>
                <div>
                    <p class="text-sm font-extrabold text-orange-300">{{ __('footer.services_kitchen') }}</p>
                    <div class="hc-footer-muted mt-4 grid gap-2.5 text-sm [&>a]:inline-flex [&>a]:min-h-[24px] [&>a]:items-center">
                        @if(turkce_sayfa() && $siteFeatures->enabled('services'))
                        <a class="font-semibold text-orange-300 hover:text-white" href="{{ route('private-chef.index') }}">👨‍🍳 Private Chef Cyprus</a>
                        <a class="font-semibold text-orange-300 hover:text-white" href="{{ route('catering.index') }}">🍽️ Catering Cyprus</a>
                        <a class="font-semibold text-orange-300 hover:text-white" href="{{ route('cooking-classes.index') }}">🧀 Yemek Kursları & Atölye</a>
                        <a class="font-semibold text-orange-300 hover:text-white" href="{{ route('meal-plans.index') }}">🥗 Akdeniz Yemek Planları</a>
                        @endif
                        @if(url()->current() !== route('cuisines.show', 'kibris-mutfagi'))
                            <a class="hover:text-orange-300" href="{{ route('cuisines.show', 'kibris-mutfagi') }}">{{ __('footer.cypriot_food') }}</a>
                        @else
                            <span class="font-semibold text-gold-300">{{ __('footer.cypriot_food') }}</span>
                        @endif
                        {{-- Bağlantı yalnız sayfa o dilde gerçekten varken basılır:
                             eşik çevrilmiş tarif sayısına bakıyor ve İngilizcede
                             hellim sayfası açılmıyordu. Alt bilgi her sayfada
                             olduğu için bu, site genelinde kırık bir bağlantıydı. --}}
                        @if(\App\Http\Controllers\IngredientHubController::sayfasiVarMi('hellim'))
                            <a class="hover:text-orange-300" href="{{ route('ingredients.hub.show', 'hellim') }}">Hellim</a>
                        @endif
                        <a class="hover:text-orange-300" href="{{ route('blog.index', ['q' => 'Kıbrıs']) }}">{{ __('footer.cyprus_guides') }}</a>
                    </div>
                </div>

            </div>
            <div class="border-t border-white/10"><div class="hc-container hc-footer-muted flex flex-col gap-2 py-5 text-xs sm:flex-row sm:items-center sm:justify-between"><span>© {{ date('Y') }} House Chef Cyprus</span><span>{{ __('footer.rights_reserved') }}</span></div></div>
        </footer>
