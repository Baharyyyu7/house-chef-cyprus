<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->withCount(['favoriteRecipes', 'pantryItems', 'cookingSessions'])
            ->when($request->filled('q'), fn ($query) => $query->where(fn ($nested) => $nested
                ->where('name', 'like', '%'.$request->string('q')->toString().'%')
                ->orWhere('email', 'like', '%'.$request->string('q')->toString().'%')))
            ->when($request->filled('role'), fn ($query) => $query->where('role', $request->string('role')->toString()))
            ->when($request->filled('status'), fn ($query) => $query->where('account_status', $request->string('status')->toString()))
            ->when($request->filled('verified'), fn ($query) => $request->string('verified')->toString() === 'yes' ? $query->whereNotNull('email_verified_at') : $query->whereNull('email_verified_at'))
            ->orderByDesc('last_activity_at')
            ->paginate(25)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user): View
    {
        return view('admin.users.show', [
            'user' => $user->loadCount(['favoriteRecipes', 'pantryItems', 'cookingSessions']),
            'activities' => $user->activities()->latest('occurred_at')->paginate(20, ['*'], 'activity_page'),
            'audits' => AuditLog::query()->where('user_id', $user->id)->latest()->limit(20)->get(),
        ]);
    }

    public function suspend(User $user): RedirectResponse
    {
        abort_if($user->is(auth()->user()), 422, 'Kendi hesabınızı bu ekrandan askıya alamazsınız.');
        $user->update(['account_status' => 'suspended']);
        $this->removeSessions($user);
        $this->audit($user, 'user.account_suspended');

        return back()->with('status', 'Kullanıcı askıya alındı ve oturumları kapatıldı.');
    }

    public function reactivate(User $user): RedirectResponse
    {
        $user->update(['account_status' => 'active']);
        $this->audit($user, 'user.account_reactivated');

        return back()->with('status', 'Kullanıcı hesabı yeniden etkinleştirildi.');
    }

    public function revokeSessions(User $user): RedirectResponse
    {
        $this->removeSessions($user);
        $this->audit($user, 'user.sessions_revoked');

        return back()->with('status', 'Kullanıcının açık oturumları kapatıldı.');
    }

    private function removeSessions(User $user): void
    {
        if (DB::getSchemaBuilder()->hasTable('sessions')) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }
    }

    private function audit(User $user, string $action): void
    {
        AuditLog::query()->create([
            'user_id' => auth()->id(),
            'action' => $action,
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'metadata' => ['user_public_id' => (string) $user->getAttribute('public_id')],
        ]);
    }
}
