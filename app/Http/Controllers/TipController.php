<?php

namespace App\Http\Controllers;

use App\Models\TipContribution;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TipController extends Controller
{
    public function process(Request $request): RedirectResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'message' => 'nullable|string|max:500',
        ]);

        $amount = (float) $request->input('amount');
        $user = auth()->user();

        TipContribution::create([
            'user_id' => $user?->id,
            'supporter_name' => $user ? $user->name : 'Anonim Gurme',
            'supporter_email' => $user?->email,
            'amount' => $amount,
            'currency' => 'EUR',
            'message' => $request->input('message'),
            'payment_provider' => 'stripe_checkout',
            'provider_payment_id' => 'tip_sim_' . uniqid(),
            'status' => 'completed',
        ]);

        return back()->with('status', 'House Chef Cyprus\'a verdiğiniz harika destek için çok teşekkür ederiz! ☕❤️');
    }
}
