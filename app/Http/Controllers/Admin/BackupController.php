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
        $disk = Storage::disk((string) config('observability.backup_disk', 'local'));
        $base = trim((string) config('observability.backup_path', 'backups'), '/').'/'.$id;
        $manifest = preg_match('/^[0-9A-HJKMNP-TV-Z]{26}$/', $id) === 1 && $disk->exists($base.'/manifest.json')
            ? json_decode((string) $disk->get($base.'/manifest.json'), true)
            : null;
        $file = is_array($manifest) ? ($manifest['database_file'] ?? null) : null;
        abort_unless(is_string($file) && in_array($file, ['database.sqlite', 'database.sql.gz'], true) && $disk->exists($base.'/'.$file), 404);

        return $disk->download($base.'/'.$file, 'backup-'.$id.'.'.($file === 'database.sql.gz' ? 'sql.gz' : 'sqlite'));
    }

    private function admin(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }
}
