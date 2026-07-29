<x-layouts.site title="Kullanıcılar">
    <section class="hc-admin-crud-page">
        <x-admin.page-header eyebrow="Gizlilik odaklı yönetim" title="Kullanıcılar" description="Yalnız destek ve hesap güvenliği için gerekli olan özet alanlar gösterilir; parola, token ve oturum içerikleri asla gösterilmez." />

        <x-admin.filter-bar class="mt-6">
            <label class="sr-only" for="user-search">Kullanıcı ara</label>
            <input id="user-search" class="hc-input" name="q" value="{{ request('q') }}" placeholder="Ad veya e-posta ara">
            <select class="hc-input" name="role"><option value="">Tüm roller</option><option value="admin" @selected(request('role') === 'admin')>Yönetici</option><option value="member" @selected(request('role') === 'member')>Üye</option></select>
            <select class="hc-input" name="status"><option value="">Tüm durumlar</option><option value="active" @selected(request('status') === 'active')>Aktif</option><option value="suspended" @selected(request('status') === 'suspended')>Askıda</option></select>
            <select class="hc-input" name="verified"><option value="">E-posta durumu</option><option value="yes" @selected(request('verified') === 'yes')>Doğrulandı</option><option value="no" @selected(request('verified') === 'no')>Doğrulanmadı</option></select>
        </x-admin.filter-bar>

        <div class="hc-admin-table-shell mt-6">
            <div class="hc-admin-table-shell__meta"><strong>{{ $users->total() }} kullanıcı</strong><span>IP bilgileri varsayılan olarak maskelidir.</span></div>
            <div class="hc-table-wrap"><table class="hc-table hc-admin-data-table"><thead><tr><th>Kullanıcı</th><th>Durum</th><th>Son aktivite</th><th>Güvenli özet</th><th></th></tr></thead><tbody>
                @forelse($users as $user)<tr>
                    <td><strong>{{ $user->name }}</strong><div class="text-xs text-muted">{{ $user->email }} · {{ $user->public_id }}</div></td>
                    <td><span class="hc-admin-status hc-admin-status--{{ $user->account_status === 'active' ? 'success' : 'neutral' }}">{{ $user->account_status === 'active' ? 'Aktif' : 'Askıda' }}</span><div class="text-xs text-muted">{{ $user->role === 'admin' ? 'Yönetici' : 'Üye' }} · {{ $user->email_verified_at ? 'E-posta doğrulandı' : 'E-posta doğrulanmadı' }}</div></td>
                    <td><span class="text-sm">{{ $user->last_activity_at?->diffForHumans() ?? 'Henüz yok' }}</span><div class="text-xs text-muted">Son giriş: {{ $user->last_login_at?->diffForHumans() ?? 'Henüz yok' }}</div></td>
                    <td><span class="text-sm">{{ $user->favorite_recipes_count }} favori · {{ $user->pantry_items_count }} dolap</span><div class="text-xs text-muted">{{ $user->cooking_sessions_count }} pişirme oturumu · {{ $user->last_login_ip_masked ?: 'IP kaydı yok' }}</div></td>
                    <td><a class="hc-v3-btn hc-v3-btn--secondary hc-v3-btn--sm" href="{{ route('admin.users.show', $user) }}">Güvenli detay</a></td>
                </tr>@empty<tr><td colspan="5"><div class="hc-admin-empty"><strong>Kullanıcı bulunamadı</strong><p>Arama ve filtreleri kontrol edin.</p></div></td></tr>@endforelse
            </tbody></table></div>
            <div class="mt-5">{{ $users->links() }}</div>
        </div>
    </section>
</x-layouts.site>
