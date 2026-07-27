<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SystemEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class SystemEventController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
        $events = SystemEvent::query()->when($request->severity, fn ($q, $v) => $q->where('severity', $v))->when($request->event_type, fn ($q, $v) => $q->where('event_type', $v))->when($request->request_id, fn ($q, $v) => $q->where('correlation_id', $v))->when($request->status === 'open', fn ($q) => $q->whereNull('resolved_at'))->when($request->status === 'resolved', fn ($q) => $q->whereNotNull('resolved_at'))->latest()->paginate(30)->withQueryString();

        return view('admin.system-events.index', compact('events'));
    }

    public function show(SystemEvent $systemEvent): View
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        return view('admin.system-events.show', ['event' => $systemEvent]);
    }

    public function resolve(SystemEvent $systemEvent): RedirectResponse
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
        if ($systemEvent->resolved_at === null) {
            $systemEvent->update(['resolved_at' => now()]);
            AuditLog::create(['user_id' => auth()->id(), 'action' => 'system_event.resolve', 'auditable_type' => SystemEvent::class, 'auditable_id' => $systemEvent->id]);
        }

        return back()->with('status', 'Olay çözüldü olarak işaretlendi.');
    }

    public function reopen(SystemEvent $systemEvent): RedirectResponse
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
        if ($systemEvent->resolved_at !== null) {
            $systemEvent->update(['resolved_at' => null]);
            AuditLog::create(['user_id' => auth()->id(), 'action' => 'system_event.reopen', 'auditable_type' => SystemEvent::class, 'auditable_id' => $systemEvent->id]);
        }

        return back()->with('status', 'Olay yeniden açıldı.');
    }
}
