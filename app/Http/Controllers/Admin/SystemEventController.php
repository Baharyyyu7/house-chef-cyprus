<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemEvent;
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
}
