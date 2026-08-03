<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class FailedJobController extends Controller
{
    public function index(Request $request): View
    {
        $this->admin();
        $jobs = DB::table('failed_jobs')
            ->when($request->string('queue')->isNotEmpty(), fn ($query) => $query->where('queue', $request->string('queue')->toString()))
            ->when($request->string('job')->isNotEmpty(), fn ($query) => $query->where('payload', 'like', '%'.addcslashes($request->string('job')->toString(), '%_\\').'%'))
            ->when($request->string('retryable')->toString() === 'yes', fn ($query) => $query->where('payload', 'like', '%"job"%'))
            ->when($request->string('retryable')->toString() === 'no', fn ($query) => $query->where('payload', 'not like', '%"job"%'))
            ->when($request->date('from'), fn ($query, $from) => $query->where('failed_at', '>=', $from->startOfDay()))
            ->when($request->date('to'), fn ($query, $to) => $query->where('failed_at', '<=', $to->endOfDay()))
            ->latest('failed_at')
            ->paginate(30)
            ->withQueryString();

        $jobs->getCollection()->transform(fn (object $job): object => $this->present($job));

        return view('admin.failed-jobs.index', compact('jobs'));
    }

    public function show(string $uuid): View
    {
        $this->admin();
        $job = DB::table('failed_jobs')->where('uuid', $uuid)->first();
        if ($job === null) {
            abort(404);
        }

        return view('admin.failed-jobs.show', ['job' => $this->present($job)]);
    }

    public function retry(string $uuid): RedirectResponse
    {
        $this->admin();
        $this->jobOrFail($uuid);
        Artisan::call('queue:retry', ['id' => [$uuid]]);
        $this->audit('queue.failed_job.retry', $uuid);

        return to_route('admin.failed-jobs.index')->with('status', 'İş güvenli olarak yeniden deneme kuyruğuna alındı.');
    }

    public function forget(string $uuid): RedirectResponse
    {
        $this->admin();
        $this->jobOrFail($uuid);
        Artisan::call('queue:forget', ['id' => $uuid]);
        $this->audit('queue.failed_job.forget', $uuid);

        return to_route('admin.failed-jobs.index')->with('status', 'Başarısız iş kaydı unutuldu.');
    }

    public function retryMany(Request $request): RedirectResponse
    {
        $this->admin();
        $data = $request->validate(['uuids' => ['required', 'array', 'max:50'], 'uuids.*' => ['uuid']]);
        $uuids = array_values(array_unique($data['uuids']));
        foreach ($uuids as $uuid) {
            $this->jobOrFail($uuid);
        }
        Artisan::call('queue:retry', ['id' => $uuids]);
        foreach ($uuids as $uuid) {
            $this->audit('queue.failed_job.retry', $uuid);
        }

        return to_route('admin.failed-jobs.index')->with('status', count($uuids).' iş yeniden deneme kuyruğuna alındı.');
    }

    private function present(object $job): object
    {
        $payload = json_decode((string) $job->payload, true);
        $job->job_class = is_array($payload) && is_string($payload['displayName'] ?? null) ? class_basename($payload['displayName']) : 'Bilinmeyen iş';
        $command = is_array($payload) && is_array($payload['data'] ?? null) ? (string) ($payload['data']['command'] ?? '') : '';
        $job->entity_public_id = preg_match('/recipePublicId";s:\\d+:"([0-9A-HJKMNP-TV-Z]{26})"/', $command, $matches) === 1 ? $matches[1] : null;
        $job->attempts = is_array($payload) && is_int($payload['maxTries'] ?? null) ? $payload['maxTries'] : null;
        $job->safe_error_code = 'QUEUE_JOB_FAILED';
        $job->safe_error_summary = 'İş güvenli olarak başarısız kaydedildi. Ayrıntılar yönetim ekranında gösterilmez.';
        $job->retryable = true;

        return $job;
    }

    private function audit(string $action, string $uuid): void
    {
        AuditLog::create(['user_id' => auth()->id(), 'action' => $action, 'auditable_type' => 'failed_job', 'auditable_id' => 0, 'metadata' => ['job_uuid' => $uuid]]);
    }

    private function jobOrFail(string $uuid): void
    {
        abort_unless(DB::table('failed_jobs')->where('uuid', $uuid)->exists(), 404);
    }

    private function admin(): void
    {
        $this->authorizeAdminOperations();
    }
}
