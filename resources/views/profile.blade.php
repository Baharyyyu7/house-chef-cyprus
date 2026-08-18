<x-layouts.site title="Profilim">
    <section class="hc-container pt-10">
        <p class="hc-kicker">Hesap ayarları</p>
        <h1 class="mt-2 hc-display-xl">Profilim</h1>
    </section>

    
    <div class="space-y-6 hc-v3-section-reveal">
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-ui.card>
                <p class="hc-kicker">Hesap</p>
                <p class="mt-2 hc-display-sm text-[var(--hc-text-primary)]">{{ auth()->user()->name }}</p>
                <p class="mt-1 hc-body-sm text-[var(--hc-text-secondary)]">{{ auth()->user()->hasVerifiedEmail() ? 'E-posta doğrulandı' : 'E-posta doğrulama bekliyor' }}</p>
            </x-ui.card>
            <x-ui.card>
                <strong class="text-2xl text-[var(--hc-text-primary)]">{{ auth()->user()->favoriteRecipes()->count() }}</strong>
                <p class="hc-body-sm text-[var(--hc-text-secondary)]">favori tarif</p>
            </x-ui.card>
            <x-ui.card>
                <strong class="text-2xl text-[var(--hc-text-primary)]">{{ auth()->user()->pantryItems()->count() }}</strong>
                <p class="hc-body-sm text-[var(--hc-text-secondary)]">dolap ürünü</p>
            </x-ui.card>
            <x-ui.card>
                <strong class="text-2xl text-[var(--hc-text-primary)]">{{ auth()->user()->cookingSessions()->count() }}</strong>
                <p class="hc-body-sm text-[var(--hc-text-secondary)]">pişirme oturumu</p>
            </x-ui.card>
        </section>
        <x-ui.card>
            <div class="max-w-xl">
                <livewire:profile.update-profile-information-form />
            </div>
        </x-ui.card>
        <x-ui.card>
            <div class="max-w-xl">
                <livewire:profile.update-password-form />
            </div>
        </x-ui.card>
        <x-ui.card variant="danger">
            <div class="max-w-xl">
                <livewire:profile.delete-user-form />
            </div>
        </x-ui.card>
    </div>
</x-layouts.site>
