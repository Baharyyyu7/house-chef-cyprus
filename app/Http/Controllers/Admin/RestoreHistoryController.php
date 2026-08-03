<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RestoreHistory;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class RestoreHistoryController extends Controller
{
    public function index(Request $r): View
    {
        $this->authorizeAdminOperations();
        $items = RestoreHistory::query()->when($r->status, fn ($q, $v) => $q->where('status', $v))->when($r->target, fn ($q, $v) => $q->where('target', $v))->when($r->backup_id, fn ($q, $v) => $q->where('backup_id', 'like', '%'.$v.'%'))->latest()->paginate(30)->withQueryString();

        return view('admin.restore-history.index', compact('items'));
    }

    public function show(RestoreHistory $restoreHistory): View
    {
        $this->authorizeAdminOperations();

        return view('admin.restore-history.show', ['item' => $restoreHistory]);
    }
}
