        <footer class="hc-footer mt-20">
            <div class="hc-container grid gap-10 py-14 md:grid-cols-2 lg:grid-cols-[1.3fr_.75fr_.85fr_.85fr_1fr]">
                <div><x-brand-logo variant="dark" /><p class="hc-footer-muted mt-5 max-w-sm text-sm leading-7">Kıbrıs sıcaklığını modern mutfak deneyimiyle bir araya getiriyoruz. Her tarif, daha keyifli bir ev sofrası için.</p><p class="hc-footer-muted mt-6 text-xs leading-5">Sosyal kanallar yalnız doğrulanmış kurumsal hesaplar yayına alındığında burada yer alır.</p></div>
                <div><p class="text-sm font-extrabold text-orange-300">Keşfet</p><div class="hc-footer-muted mt-4 grid gap-3 text-sm [&>a]:inline-flex [&>a]:min-h-[24px] [&>a]:items-center"><a class="hover:text-orange-300" href="{{ route('recipes.index') }}">Tarifler</a><a class="hover:text-orange-300" href="{{ route('blog.index') }}">Blog</a><a class="hover:text-orange-300" href="{{ route('cuisines.index') }}">Mutfaklar</a><a class="hover:text-orange-300" href="{{ route('categories.index') }}">Kategoriler</a><a class="hover:text-orange-300" href="{{ route('ingredients.hub.index') }}">Malzemeler</a><a class="hover:text-orange-300" href="{{ route('collections.sure.index') }}">Süreye göre</a><a class="hover:text-orange-300" href="{{ route('collections.zorluk.index') }}">Zorluğa göre</a><a class="hover:text-orange-300" href="{{ route('ingredients.explore') }}">Ne Pişirebilirim?</a>@auth<a class="hover:text-orange-300" href="{{ route('pantry.index') }}">Malzeme Dolabım</a>@endauth</div></div>
                {{-- On bir baglanti tek dar sutunun icinde iki sutuna sikistirilmisti;
                     "Sik sorulan / sorular" ve "Yasal / bilgilendirme" diye
                     kiriliyordu. Iki ayri baslik, her biri tek sutun. --}}
                <div>
                    <p class="text-sm font-extrabold text-orange-300">Platform</p>
                    <div class="hc-footer-muted mt-4 grid gap-3 text-sm [&>a]:inline-flex [&>a]:min-h-[24px] [&>a]:items-center">
                        <a class="whitespace-nowrap hover:text-orange-300" href="{{ route('information.show', 'hakkimizda') }}">Hakkımızda</a>
                        <a class="whitespace-nowrap hover:text-orange-300" href="{{ route('information.show', 'nasil-calisir') }}">Nasıl çalışır?</a>
                        <a class="whitespace-nowrap hover:text-orange-300" href="{{ route('information.show', 'sss') }}">Sıkça sorulanlar</a>
                        <a class="whitespace-nowrap hover:text-orange-300" href="{{ route('information.show', 'tarif-dogrulama-politikasi') }}">Tarif doğrulama</a>
                        <a class="whitespace-nowrap hover:text-orange-300" href="{{ route('information.show', 'editorial-ilkeler') }}">Editoryal ilkeler</a>
                        <a class="whitespace-nowrap hover:text-orange-300" href="{{ route('information.show', 'hata-bildir') }}">Hata bildir</a>
                        <a class="whitespace-nowrap hover:text-orange-300" href="{{ route('contact.index') }}">İletişim</a>
                    </div>
                </div>

                <div>
                    <p class="text-sm font-extrabold text-orange-300">Yasal</p>
                    <div class="hc-footer-muted mt-4 grid gap-3 text-sm [&>a]:inline-flex [&>a]:min-h-[24px] [&>a]:items-center">
                        <a class="whitespace-nowrap hover:text-orange-300" href="{{ route('legal.center') }}#gizlilik-ve-kisisel-veriler">Gizlilik</a>
                        <a class="whitespace-nowrap hover:text-orange-300" href="{{ route('legal.center') }}#kullanim-kosullari">Kullanım koşulları</a>
                        <a class="whitespace-nowrap hover:text-orange-300" href="{{ route('legal.center') }}#cerez-politikasi">Çerezler</a>
                        <a class="whitespace-nowrap hover:text-orange-300" href="{{ route('legal.center') }}#gorsel-ve-yapay-zeka-politikasi">Görsel politikası</a>
                        <a class="whitespace-nowrap hover:text-orange-300" href="{{ route('legal.center') }}#icerik-ve-kaynak-politikasi">Kaynak politikası</a>
                        <a class="whitespace-nowrap hover:text-orange-300" href="{{ route('information.show', 'besin-degeri-aciklamasi') }}">Besin değeri açıklaması</a>
                        <a class="whitespace-nowrap hover:text-orange-300" href="{{ route('information.show', 'alerjen-uyarisi') }}">Alerjen uyarısı</a>
                        <a class="whitespace-nowrap hover:text-orange-300" href="{{ route('information.show', 'gorsel-ve-medya-politikasi') }}">Görsel ve medya</a>
                        <a class="whitespace-nowrap hover:text-orange-300" href="{{ route('information.show', 'icerik-duzeltme-politikasi') }}">İçerik düzeltme</a>
                    </div>
                </div>
                <div><p class="text-sm font-extrabold text-orange-300">Bülten</p><p class="hc-footer-muted mt-4 text-sm leading-6">Yeni tarifler ve mutfak ilhamı için bizi takipte kalın.</p><a href="{{ route('register') }}" class="hc-btn hc-btn-primary mt-5">Ücretsiz üye ol</a></div>
            </div>
            <div class="border-t border-white/10"><div class="hc-container hc-footer-muted flex flex-col gap-2 py-5 text-xs sm:flex-row sm:items-center sm:justify-between"><span>© {{ date('Y') }} House Chef Cyprus</span><span>Özgün editoryal içerik · Gizlilik odaklı platform</span></div></div>
        </footer>
