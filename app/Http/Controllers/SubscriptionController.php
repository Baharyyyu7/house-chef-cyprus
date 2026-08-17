<?php

namespace App\Http\Controllers;

use App\Models\UserSubscription;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function pricing(): View
    {
        $plans = config('monetization.plans', []);
        return view('pricing', compact('plans'));
    }

    public function checkout(Request $request): RedirectResponse
    {
        $request->validate([
            'plan' => 'required|in:monthly,yearly',
        ]);

        $user = $request->user();
        $planKey = $request->input('plan');
        $planConfig = config("monetization.plans.{$planKey}");

        if (!$planConfig) {
            return back()->with('error', 'Geçersiz abonelik planı seçildi.');
        }

        // Demo / Simülasyon ödeme işlemi (Stripe API Keys veya Iyzico aktifleşene kadar instant activation)
        $expiresAt = $planKey === 'yearly' ? now()->addYear() : now()->addMonth();

        // Kullanıcıyı premium yap
        $user->update([
            'is_premium' => true,
            'premium_plan' => $planKey,
            'premium_expires_at' => $expiresAt,
        ]);

        // Abonelik kaydı ekle
        UserSubscription::create([
            'user_id' => $user->id,
            'plan' => $planKey,
            'amount' => $planConfig['price'],
            'currency' => $planConfig['currency'],
            'payment_provider' => 'stripe_checkout',
            'provider_subscription_id' => 'sub_sim_' . uniqid(),
            'status' => 'active',
            'started_at' => now(),
            'expires_at' => $expiresAt,
        ]);

        return redirect()->route('dashboard')->with('status', 'Tebrikler! House Chef Premium üyeliğiniz başarıyla aktif edildi. Sınırsız AI mutfak ve reklamsız deneyimin tadını çıkarın!');
    }

    public function cancel(Request $request): RedirectResponse
    {
        $user = $request->user();

        $activeSubscription = $user->subscriptions()->where('status', 'active')->first();
        if ($activeSubscription) {
            $activeSubscription->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);
        }

        return back()->with('status', 'Abonelik yenilemeniz iptal edildi. Mevcut süreniz bitene kadar Premium avantajlarından yararlanmaya devam edebilirsiniz.');
    }
}
