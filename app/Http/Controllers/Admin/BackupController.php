<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BackupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

final class BackupController extends Controller
{
    public function index(BackupService $service): View
    {
        $this->admin();

        return view('admin.backups.index', ['backups' => $service->list()]);
    }

    public function create(BackupService $service): RedirectResponse
    {
        $this->admin();
        $service->create();

        return back()->with('status', 'Backup oluşturuldu.');
    }

    public function verify(string $id, BackupService $service): RedirectResponse
    {
        $this->admin();
        $service->verify($id);

        return back()->with('status', 'Backup doğrulandı.');
    }

    public function cleanup(BackupService $service): RedirectResponse
    {
        $this->admin();
        $r = $service->cleanup();

        return back()->with('status', $r['candidates'].' aday dry-run olarak bulundu.');
    }

    public function download(string $id)
    {
        $this->admin();
        abort_unless(preg_match('/^[0-9A-HJKMNP-TV-Z]{26}$/', $id) === 1 && Storage::disk('local')->exists('backups/'.$id.'/database.sqlite'), 404);

        return Storage::disk('local')->download('backups/'.$id.'/database.sqlite', 'backup-'.$id.'.sqlite');
    }

    private function admin(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }
}
