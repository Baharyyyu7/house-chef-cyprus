<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public string $password = '';

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/');
    }
}; ?>

<section class="space-y-6">
    <header>
        <h2 class="hc-heading-3" style="color:var(--hc-text-danger)">
            Hesabı sil
        </h2>

        <p class="mt-1 hc-body-sm" style="color:var(--hc-text-secondary)">
            Hesabınız silindiğinde kişisel verileriniz kalıcı olarak kaldırılır. Saklamak istediğiniz bilgileri önce dışa aktarın.
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >Hesabı sil</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="deleteUser" class="p-6">

            <h2 class="hc-heading-3">
                Hesabınızı silmek istediğinizden emin misiniz?
            </h2>

            <p class="mt-1 hc-body-sm" style="color:var(--hc-text-secondary)">
                Bu işlem geri alınamaz. Devam etmek için mevcut parolanızı girin.
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="Parola" class="sr-only" />

                <x-text-input
                    wire:model="password"
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4"
                    placeholder="Mevcut parola"
                />

                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Vazgeç
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    Hesabı kalıcı olarak sil
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
