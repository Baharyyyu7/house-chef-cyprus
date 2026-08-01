<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiInteraction;
use App\Services\AI\AiKitchenService;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AiKitchenController extends Controller
{
    public function index(Request $request, AiKitchenService $kitchen): View
    {
        $query = AiInteraction::query()->with('user:id,public_id,name')->latest();
        if ($request->filled('provider')) {
            $provider = $request->string('provider')->toString();
            $query->where('provider', $provider === 'local' ? 'mock' : $provider);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return view('admin.ai-kitchen.index', ['stats' => $kitchen->stats(), 'interactions' => $query->paginate(30)->withQueryString()]);
    }
}
