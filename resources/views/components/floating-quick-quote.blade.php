@props([])

@php
    $whatsappNumber = config('contact-requests.whatsapp_number', '905338000000');
@endphp

<div x-data="{
    open: false,
    service: 'private_chef',
    region: 'Girne',
    guests: '5-10',
    name: '',
    phone: '',
    date: '',
    get whatsappUrl() {
        const text = encodeURIComponent(
            `Merhaba House Chef Cyprus! Özel Şef / Catering teklifi almak istiyorum.\n\n` +
            `• Hizmet: ${this.service === 'private_chef' ? 'Özel Şef (Villa Deneyimi)' : (this.service === 'catering' ? 'Catering & Davet' : (this.service === 'cooking_class' ? 'Yemek Kursu' : 'Haftalık Menü Planı'))}\n` +
            `• Bölge: ${this.region}\n` +
            `• Kişi Sayısı: ${this.guests}\n` +
            (this.date ? `• Tarih: ${this.date}\n` : '') +
            (this.name ? `• İsim: ${this.name}\n` : '') +
            (this.phone ? `• Tel: ${this.phone}\n` : '') +
            `\nFiyat ve menü detaylarını iletebilir misiniz?`
        );
        return `https://api.whatsapp.com/send?phone={{ $whatsappNumber }}&text=${text}`;
    }
}" 
x-on:keydown.escape.window="open = false" 
class="relative z-40">

    {{-- Floating Action Pill --}}
    <div class="fixed bottom-5 right-5 z-40 flex items-center gap-2">
        <button type="button" 
                x-on:click="open = true" 
                class="group flex items-center gap-2.5 rounded-full border border-orange-400/40 bg-gradient-to-r from-orange-600 via-amber-600 to-orange-700 px-4 py-3 text-white shadow-xl shadow-orange-950/20 transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:shadow-orange-900/30 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 dark:ring-offset-stone-900"
                aria-haspopup="dialog"
                x-bind:aria-expanded="open.toString()"
                aria-label="Kuzey Kıbrıs Özel Şef & Catering Hızlı Teklif Al">
            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-white/20 text-base backdrop-blur-sm transition-transform group-hover:rotate-12" aria-hidden="true">
                👨‍🍳
            </span>
            <span class="flex flex-col text-left">
                <span class="text-[10px] font-bold uppercase tracking-widest text-orange-200">Kıbrıs Özel Şef</span>
                <span class="text-xs font-black sm:text-sm">Hızlı Teklif Al</span>
            </span>
            <span class="ml-1 inline-flex items-center rounded-full bg-emerald-500/90 px-2 py-0.5 text-[10px] font-bold text-white shadow-sm animate-pulse">
                Canlı
            </span>
        </button>
    </div>

    {{-- Modal Backdrop & Dialog --}}
    <div x-show="open" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto bg-stone-950/70 p-4 backdrop-blur-sm sm:p-6"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         role="dialog" 
         aria-modal="true" 
         aria-labelledby="quick-quote-title">
        
        <div class="flex min-h-full items-center justify-center">
            <div x-show="open"
                 x-on:click.outside="open = false"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                 class="w-full max-w-lg rounded-3xl border border-stone-200/90 bg-white p-6 shadow-2xl dark:border-stone-800 dark:bg-stone-900 sm:p-8">
                
                {{-- Modal Header --}}
                <div class="flex items-start justify-between gap-4 border-b border-stone-150 pb-4 dark:border-stone-800">
                    <div>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-orange-100 px-3 py-1 text-xs font-bold text-orange-700 dark:bg-orange-950/60 dark:text-orange-300">
                            <span aria-hidden="true">✨</span> Ada Geneli Villa & Davet Hizmeti
                        </span>
                        <h2 id="quick-quote-title" class="mt-2 text-xl font-black text-stone-900 dark:text-white sm:text-2xl">
                            Özel Şef & Catering Teklifi Al
                        </h2>
                        <p class="mt-1 text-xs text-stone-600 dark:text-stone-300">
                            Girne, Lefkoşa, Mağusa ve İskele'de villanız için anında fiyat teklifi hazırlayalım.
                        </p>
                    </div>
                    <button type="button" 
                            x-on:click="open = false" 
                            class="rounded-full p-2 text-stone-400 transition hover:bg-stone-100 hover:text-stone-700 dark:hover:bg-stone-800 dark:hover:text-stone-200"
                            aria-label="Kapat">
                        <span class="text-xl" aria-hidden="true">✕</span>
                    </button>
                </div>

                {{-- Interactive Quick Configurator --}}
                <div class="mt-6 space-y-4">
                    {{-- Hizmet Seçimi --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300">
                            1. İstenen Hizmet
                        </label>
                        <div class="mt-2 grid grid-cols-2 gap-2 text-xs font-bold">
                            <button type="button" 
                                    x-on:click="service = 'private_chef'"
                                    x-bind:class="service === 'private_chef' ? 'border-orange-500 bg-orange-50 text-orange-800 dark:bg-orange-950/50 dark:text-orange-200' : 'border-stone-200 bg-stone-50 text-stone-700 hover:border-stone-300 dark:border-stone-700 dark:bg-stone-800 dark:text-stone-300'"
                                    class="rounded-xl border p-2.5 text-center transition">
                                👨‍🍳 Özel Şef (Villa)
                            </button>
                            <button type="button" 
                                    x-on:click="service = 'catering'"
                                    x-bind:class="service === 'catering' ? 'border-orange-500 bg-orange-50 text-orange-800 dark:bg-orange-950/50 dark:text-orange-200' : 'border-stone-200 bg-stone-50 text-stone-700 hover:border-stone-300 dark:border-stone-700 dark:bg-stone-800 dark:text-stone-300'"
                                    class="rounded-xl border p-2.5 text-center transition">
                                🥂 Catering & Davet
                            </button>
                            <button type="button" 
                                    x-on:click="service = 'cooking_class'"
                                    x-bind:class="service === 'cooking_class' ? 'border-orange-500 bg-orange-50 text-orange-800 dark:bg-orange-950/50 dark:text-orange-200' : 'border-stone-200 bg-stone-50 text-stone-700 hover:border-stone-300 dark:border-stone-700 dark:bg-stone-800 dark:text-stone-300'"
                                    class="rounded-xl border p-2.5 text-center transition">
                                📚 Yemek Kursu
                            </button>
                            <button type="button" 
                                    x-on:click="service = 'meal_plan'"
                                    x-bind:class="service === 'meal_plan' ? 'border-orange-500 bg-orange-50 text-orange-800 dark:bg-orange-950/50 dark:text-orange-200' : 'border-stone-200 bg-stone-50 text-stone-700 hover:border-stone-300 dark:border-stone-700 dark:bg-stone-800 dark:text-stone-300'"
                                    class="rounded-xl border p-2.5 text-center transition">
                                🥗 Haftalık Menü
                            </button>
                        </div>
                    </div>

                    {{-- Bölge & Kişi Sayısı --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="quick_region" class="block text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300">
                                2. Bölge
                            </label>
                            <select id="quick_region" x-model="region" class="hc-v3-input mt-1.5 text-sm py-2">
                                <option value="Girne">Girne & Çevresi</option>
                                <option value="Lefkoşa">Lefkoşa</option>
                                <option value="Gazimağusa">Gazimağusa</option>
                                <option value="İskele">İskele & Karpaz</option>
                                <option value="Güzelyurt">Güzelyurt & Lefke</option>
                            </select>
                        </div>
                        <div>
                            <label for="quick_guests" class="block text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300">
                                3. Kişi Sayısı
                            </label>
                            <select id="quick_guests" x-model="guests" class="hc-v3-input mt-1.5 text-sm py-2">
                                <option value="2-4">2 - 4 Kişi</option>
                                <option value="5-10">5 - 10 Kişi</option>
                                <option value="11-20">11 - 20 Kişi</option>
                                <option value="21-50">21 - 50 Kişi</option>
                                <option value="50+">50+ Kişi</option>
                            </select>
                        </div>
                    </div>

                    {{-- Tarih & İletişim (İsteğe Bağlı) --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="quick_date" class="block text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300">
                                4. Tarih (İsteğe bağlı)
                            </label>
                            <input id="quick_date" x-model="date" type="date" class="hc-v3-input mt-1.5 text-sm py-2">
                        </div>
                        <div>
                            <label for="quick_name" class="block text-xs font-bold uppercase tracking-wider text-stone-700 dark:text-stone-300">
                                5. İsim (İsteğe bağlı)
                            </label>
                            <input id="quick_name" x-model="name" type="text" placeholder="Adınız" class="hc-v3-input mt-1.5 text-sm py-2">
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="mt-6 space-y-2.5 pt-4 border-t border-stone-150 dark:border-stone-800">
                    {{-- WhatsApp Direct --}}
                    <a x-bind:href="whatsappUrl" 
                       target="_blank" 
                       rel="noopener noreferrer"
                       class="flex w-full items-center justify-center gap-2.5 rounded-2xl bg-emerald-600 px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-emerald-950/20 transition hover:bg-emerald-700 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <span class="text-lg" aria-hidden="true">💬</span>
                        <span>WhatsApp ile Anında Teklif Al</span>
                        <span class="text-emerald-200">➔</span>
                    </a>

                    {{-- Form Alternative --}}
                    <div class="flex items-center justify-between pt-2">
                        <a href="{{ route('private-chef.index') }}#booking-form" 
                           x-on:click="open = false"
                           class="text-xs font-semibold text-stone-500 underline underline-offset-2 hover:text-orange-600 dark:text-stone-400">
                            Detaylı Rezervasyon Formunu Aç
                        </a>
                        <span class="text-[11px] text-stone-400 dark:text-stone-500">
                            Ön teklif tamamen ücretsizdir
                        </span>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
