<?php

namespace App\Http\Controllers;

use App\Services\Payments\StripeCheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class SubscriptionController extends Controller
{
    public function pricing(StripeCheckoutService $stripe): View
    {
        $plans = config('monetization.plans', []);
        $paymentsEnabled = $stripe->isReady();

        return view('pricing', compact('plans', 'paymentsEnabled'));
    }

    public function checkout(Request $request, StripeCheckoutService $stripe): RedirectResponse
    {
        if (! $stripe->isReady()) {
            return back()->withErrors(['payment' => 'Ödeme altyapısı yakında aktif olacaktır']);
        }

        $request->validate([
            'plan' => 'required|in:monthly,yearly',
        ]);

        $user = $request->user();

        if ($user->isPremium()) {
            return back()->withErrors(['payment' => 'Aktif bir aboneliğiniz var. Plan değişiklikleri için abonelik yönetimini kullanın.']);
        }

        $planKey = $request->input('plan');
        $planConfig = config("monetization.plans.{$planKey}");

        if (! $planConfig) {
            return back()->withErrors(['plan' => 'Geçersiz abonelik planı seçildi.']);
        }

        try {
            return redirect()->away($stripe->subscription($user, $planKey));
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors(['payment' => 'Ödeme şu anda başlatılamıyor. Premium üyelik verilmedi.']);
        }
    }

    public function cancel(Request $request, StripeCheckoutService $stripe): RedirectResponse
    {
        if (! $stripe->isReady()) {
            return back()->withErrors(['payment' => 'Ödeme sistemi kapalıyken abonelik işlemi başlatılamaz.']);
        }

        $user = $request->user();

        $activeSubscription = $user->subscriptions()
            ->where('status', 'active')
            ->where('payment_provider', 'stripe')
            ->whereNotNull('provider_subscription_id')
            ->first();

        if (! $activeSubscription || ! $activeSubscription->isPaymentVerified()) {
            return back()->withErrors(['payment' => 'Yönetilebilir aktif bir Stripe aboneliği bulunamadı.']);
        }

        try {
            $stripe->cancelAtPeriodEnd($activeSubscription->provider_subscription_id);
            $activeSubscription->update(['cancelled_at' => now()]);
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors(['payment' => 'Abonelik iptali şu anda tamamlanamadı.']);
        }

        return back()->with('status', 'Abonelik yenilemeniz iptal edildi. Mevcut süreniz bitene kadar Premium avantajlarından yararlanmaya devam edebilirsiniz.');
    }

    public function billingPortal(Request $request, StripeCheckoutService $stripe): RedirectResponse
    {
        if (! $stripe->isReady()) {
            return back()->withErrors(['payment' => 'Ödeme sistemi kapalıyken abonelik yönetimi açılamaz.']);
        }

        try {
            return redirect()->away($stripe->billingPortal($request->user()));
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors(['payment' => 'Fatura ve abonelik yönetimi şu anda açılamıyor.']);
        }
    }
}
