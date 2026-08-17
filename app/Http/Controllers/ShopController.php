<?php

namespace App\Http\Controllers;

use App\Models\DigitalOrder;
use App\Models\DigitalProduct;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function index(): View
    {
        $products = DigitalProduct::where('is_active', true)->latest()->get();
        return view('shop.index', compact('products'));
    }

    public function show(DigitalProduct $product): View
    {
        return view('shop.show', compact('product'));
    }

    public function checkout(Request $request, DigitalProduct $product): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
            'name' => 'required|string|max:255',
        ]);

        $orderToken = Str::random(32);
        $order = DigitalOrder::create([
            'order_uuid' => (string) Str::uuid(),
            'user_id' => auth()->id(),
            'digital_product_id' => $product->id,
            'buyer_email' => $request->input('email'),
            'buyer_name' => $request->input('name'),
            'amount' => $product->price,
            'currency' => $product->currency,
            'payment_provider' => 'stripe_checkout',
            'provider_payment_id' => 'pay_sim_' . uniqid(),
            'status' => 'completed',
            'download_token' => $orderToken,
            'download_count' => 0,
            'max_downloads' => 5,
        ]);

        $product->increment('sales_count');

        return redirect()->route('shop.success', $orderToken)->with('status', 'Siparişiniz başarıyla tamamlandı! Dijital ürününüzü hemen indirebilirsiniz.');
    }

    public function success(string $downloadToken): View
    {
        $order = DigitalOrder::where('download_token', $downloadToken)->firstOrFail();
        return view('shop.success', compact('order'));
    }

    public function download(string $downloadToken)
    {
        $order = DigitalOrder::where('download_token', $downloadToken)->firstOrFail();

        if (!$order->canDownload()) {
            return back()->with('error', 'Maksimum indirme limitine ulaşıldı veya sipariş geçersiz.');
        }

        $order->increment('download_count');

        // Örnek dosya indirimi / simülasyon yanıtı
        return response()->streamDownload(function () use ($order) {
            echo "House Chef Cyprus - " . $order->product->title . "\n\nBu dijital ürün satın alma işleminiz için teşekkür ederiz!\nSipariş No: " . $order->order_uuid;
        }, Str::slug($order->product->title) . '.txt');
    }
}
