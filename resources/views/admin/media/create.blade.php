<x-layouts.site title="Görsel Yükle">
    <section class="mx-auto max-w-2xl px-5 py-12">
        <a href="{{ route('admin.media.index') }}" class="text-sm font-bold text-gold-700">← Medya</a>

        <h1 class="mt-3 font-display text-4xl font-semibold">Görsel yükle</h1>
        <p class="mt-2 text-muted">
            JPEG, PNG veya WebP; en fazla {{ number_format(config('security.max_upload_bytes') / 1024 / 1024, 0) }} MB
            ve {{ number_format(config('security.max_image_pixels')) }} piksel. SVG ve çalıştırılabilir dosyalar kabul edilmez.
        </p>

        {{-- Yüklemenin sonunda ne olacağı baştan yazılı: eskiden "yüklendi"
             deyip bırakıyordu ve görsel hiçbir zaman yayına çıkmıyordu. --}}
        <p class="mt-3 rounded-xl bg-amber-50 p-3 text-sm text-amber-900">
            Yüklenen görsel doğrudan yayına çıkmaz. Onay kuyruğuna düşer;
            <strong>Medya</strong> ekranında ona bakıp onayladığınızda görünür olur.
        </p>

        @if($errors->any())
            <div class="mt-4 rounded-xl bg-red-50 p-4 text-red-800">{{ $errors->first() }}</div>
        @endif

        <form class="hc-card mt-6 space-y-5" method="post" action="{{ route('admin.media.store') }}" enctype="multipart/form-data">
            @csrf

            <label class="block">
                <span class="text-sm font-bold">Dosya</span>
                <input class="mt-1 block w-full" type="file" name="file" accept="image/jpeg,image/png,image/webp" required>
            </label>

            {{-- Tür seçimi iki listeden hangisinin görüneceğini belirler.
                 Alpine yoksa ikisi de görünür ve form yine çalışır. --}}
            <div x-data="{ tur: '{{ old('entity_type', 'recipe') }}' }">
                <label class="block">
                    <span class="text-sm font-bold">Neye bağlanacak</span>
                    <select class="hc-input mt-1 w-full" name="entity_type" x-model="tur">
                        <option value="recipe">Tarif</option>
                        <option value="ingredient">Malzeme</option>
                    </select>
                </label>

                <label class="mt-4 block" x-show="tur === 'recipe'">
                    <span class="text-sm font-bold">Tarif</span>
                    {{-- Eskiden burada 26 karakterlik ULID elle yazılıyordu.
                         Kimse bir tarifin ULID'ini bilmiyor; kayıt adıyla seçilir. --}}
                    <select class="hc-input mt-1 w-full" name="entity_public_id" x-bind:disabled="tur !== 'recipe'">
                        @foreach($tarifler as $tarif)
                            <option value="{{ $tarif->public_id }}" @selected(old('entity_public_id') === $tarif->public_id)>{{ $tarif->title }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="mt-4 block" x-show="tur === 'ingredient'" x-cloak>
                    <span class="text-sm font-bold">Malzeme</span>
                    <select class="hc-input mt-1 w-full" name="entity_public_id" x-bind:disabled="tur !== 'ingredient'">
                        @foreach($malzemeler as $malzeme)
                            <option value="{{ $malzeme->public_id }}" @selected(old('entity_public_id') === $malzeme->public_id)>{{ $malzeme->name }}</option>
                        @endforeach
                    </select>
                </label>
            </div>

            <label class="block">
                <span class="text-sm font-bold">Alt metin</span>
                <input class="hc-input mt-1 w-full" name="alt_text" value="{{ old('alt_text') }}" maxlength="255" placeholder="Görselde ne olduğunu yazın">
            </label>

            <label class="block">
                <span class="text-sm font-bold">Görünürlük</span>
                <select class="hc-input mt-1 w-full" name="visibility">
                    <option value="public">Public — yayınlanabilir</option>
                    <option value="private">Private — yalnızca yönetici</option>
                </select>
            </label>

            {{-- Kapı bu iki beyanı arıyor ve hiçbir arayüz onları yazmıyordu:
                 elle yüklenen görsel takılıp kalıyordu. Beyanı sistem uydurmaz,
                 yükleyen verir ve kim verdiği kaydedilir. --}}
            <fieldset class="rounded-xl border border-[var(--hc-line)] p-4">
                <legend class="px-1 text-sm font-bold">Beyan</legend>

                <label class="flex gap-3 text-sm">
                    <input type="checkbox" name="telif_beyani" value="1" class="mt-1" required>
                    <span>Bu görseli kullanma hakkına sahibim; telif ihlali içermiyor.</span>
                </label>

                <label class="mt-3 flex gap-3 text-sm">
                    <input type="checkbox" name="icerik_beyani" value="1" class="mt-1" required>
                    <span>Görsel, seçtiğim kaydın gerçekten kendisini gösteriyor.</span>
                </label>
            </fieldset>

            <x-ui.button>Yükle ve onaya gönder</x-ui.button>
        </form>
    </section>
</x-layouts.site>
