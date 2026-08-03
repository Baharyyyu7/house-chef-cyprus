<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    public function logout(Logout $logout): void
    {
        $logout();
        $this->redirect('/');
    }
}; ?>

<header x-data="{ open: false }" class="sticky top-0 z-40 border-b border-forest-900/15 bg-paper/95 backdrop-blur dark:border-white/10 dark:bg-forest-950/95">
    <nav class="hc-container flex h-19 items-center justify-between gap-4" aria-label="Hesap menüsü">
        <x-brand-logo />
        <div class="hidden items-center gap-5 text-sm font-semibold lg:flex">
            <a href="{{ route('dashboard') }}" class="nav-link">Hesabım</a><a href="{{ route('recipes.index') }}" class="nav-link">Tarifler</a><a href="{{ route('ingredients.explore') }}" class="nav-link">Ne Pişirebilirim?</a><a href="{{ route('pantry.index') }}" class="nav-link">Dolabım</a><a href="{{ route('favorites.index') }}" class="nav-link">Favoriler</a><a href="{{ route('collections.index') }}" class="nav-link">Koleksiyonlar</a>
            @if(auth()->user()->isAdmin())<a href="{{ route('admin.dashboard') }}" class="hc-btn hc-btn-gold">Yönetim</a>@endif
            <x-dropdown align="right" width="48"><x-slot name="trigger"><button class="hc-btn hc-btn-primary" type="button"><span x-data="{ name: @js(auth()->user()->name) }" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></span><span class="ml-2">⌄</span></button></x-slot><x-slot name="content"><x-dropdown-link :href="route('profile')">Profilim</x-dropdown-link><button wire:click="logout" class="w-full text-start"><x-dropdown-link>Çıkış yap</x-dropdown-link></button></x-slot></x-dropdown>
        </div>
        <button @click="open = !open" class="rounded-lg p-2 text-forest-900 dark:text-cream lg:hidden" :aria-expanded="open" aria-label="Menüyü aç">☰</button>
    </nav>
    <div x-cloak x-show="open" class="border-t border-forest-900/10 px-5 py-4 dark:border-white/10 lg:hidden"><div class="mx-auto grid max-w-7xl gap-2 text-sm font-semibold"><a class="mobile-nav-link" href="{{ route('dashboard') }}">Hesabım</a><a class="mobile-nav-link" href="{{ route('recipes.index') }}">Tarifler</a><a class="mobile-nav-link" href="{{ route('ingredients.explore') }}">Ne Pişirebilirim?</a><a class="mobile-nav-link" href="{{ route('pantry.index') }}">Dolabım</a><a class="mobile-nav-link" href="{{ route('favorites.index') }}">Favoriler</a><a class="mobile-nav-link" href="{{ route('collections.index') }}">Koleksiyonlar</a><a class="mobile-nav-link" href="{{ route('profile') }}">Profilim</a><button wire:click="logout" class="mobile-nav-link text-left text-red-700">Çıkış yap</button></div></div>
</header>
