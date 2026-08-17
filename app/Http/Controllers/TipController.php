<?php

namespace App\Http\Controllers;

use App\Models\TipContribution;
use App\Services\Payments\StripeCheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class TipController extends Controller
{
    public function process(Request $request, StripeCheckoutService $stripe): RedirectResponse
    {
        if (! $stripe->isReady()) {
            return back()->withErrors(['payment' => 'Ödeme sistemi henüz açık değil. Destek özelliği yakında kullanıma sunulacak.']);
        }

        $request->validate([
            'amount' => ['required', 'numeric', 'decimal:0,2', 'min:1', 'max:'.config('monetization.tip_max_amount', 250)],
            'message' => 'nullable|string|max:500',
        ]);

        $tip = TipContribution::create([
            'user_id' => auth()->id(),
            'supporter_name' => auth()->check() ? $request->user()->name : 'Anonim Gurme',
            'supporter_email' => auth()->check() ? $request->user()->email : null,
            'amount' => (float) $request->input('amount'),
            'currency' => 'EUR',
            'message' => $request->input('message'),
            'payment_provider' => 'stripe',
            'status' => 'pending',
        ]);

        try {
            return redirect()->away($stripe->tip($tip));
        } catch (Throwable $exception) {
            $tip->delete();
            report($exception);

            return back()->withErrors(['payment' => 'Ödeme şu anda başlatılamıyor. Destek kaydı oluşturulmadı.']);
        }
    }
}
