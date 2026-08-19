<?php

namespace App\Http\Controllers;

use App\Models\DigitalOrder;
use App\Models\DigitalProduct;
use App\Services\Payments\StripeCheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class ShopController extends Controller
{
    public function index(StripeCheckoutService $stripe): View
    {
        // Vitrinde yalnız dosyası gerçekten var olan ürün durur. Satın alma
        // adımı dosyayı zaten kontrol ediyordu, ama ürün listede görünüyordu:
        // "120 sayfa" yazan, arkasında hiçbir dosya olmayan bir kayıt okura
        // satılabilirmiş gibi duruyordu.
        $products = DigitalProduct::query()
            ->where('is_active', true)
            ->latest()
            ->get()
            ->filter(fn (DigitalProduct $product): bool => is_string($product->file_path)
                && $product->file_path !== ''
                && Storage::disk('local')->exists($product->file_path))
            ->values();
        $paymentsEnabled = $stripe->isReady();

        return view('shop.index', compact('products', 'paymentsEnabled'));
    }

    public function show(DigitalProduct $product): RedirectResponse
    {
        return redirect()->route('shop.index', ['product' => $product->slug]);
    }

    public function checkout(Request $request, DigitalProduct $product, StripeCheckoutService $stripe): RedirectResponse
    {
        if (! $stripe->isReady()) {
            return back()->withErrors(['payment' => 'Ödeme sistemi henüz açık değil. Dijital ürün satışı yakında kullanıma sunulacak.']);
        }

        $request->validate([
            'email' => 'required|email',
            'name' => 'required|string|max:255',
        ]);

        if (! $product->is_active) {
            abort(404);
        }

        if (! $product->file_path || ! Storage::disk('local')->exists($product->file_path)) {
            return back()->withErrors(['payment' => 'Bu dijital ürünün doğrulanmış teslimat dosyası henüz hazır değil. Ödeme başlatılmadı.']);
        }

        $order = DigitalOrder::create([
            'order_uuid' => (string) Str::uuid(),
            'user_id' => auth()->id(),
            'digital_product_id' => $product->id,
            'buyer_email' => (string) $request->string('email'),
            'buyer_name' => (string) $request->string('name'),
            'amount' => $product->price,
            'currency' => $product->currency,
            'payment_provider' => 'stripe',
            'status' => 'pending',
            'download_token' => Str::random(48),
            'download_count' => 0,
            'max_downloads' => 5,
        ]);

        try {
            return redirect()->away($stripe->digitalOrder($order));
        } catch (Throwable $exception) {
            $order->delete();
            report($exception);

            return back()->withErrors(['payment' => 'Ödeme şu anda başlatılamıyor. Sipariş oluşturulmadı.']);
        }
    }

    public function success(string $orderUuid): View
    {
        $order = DigitalOrder::where('order_uuid', $orderUuid)->firstOrFail();

        return view('shop.success', compact('order'));
    }

    public function download(string $downloadToken)
    {
        $order = DigitalOrder::query()->with('product')->where('download_token', $downloadToken)->firstOrFail();
        $filePath = $order->product->file_path;

        if (! $filePath || ! Storage::disk('local')->exists($filePath)) {
            return back()->withErrors(['download' => 'Ürün dosyası şu anda hazırlanıyor. İndirme hakkınız azaltılmadı.']);
        }

        $reserved = DB::transaction(function () use ($order): bool {
            $lockedOrder = DigitalOrder::query()->lockForUpdate()->findOrFail($order->id);

            if (! $lockedOrder->canDownload()) {
                return false;
            }

            $lockedOrder->increment('download_count');

            return true;
        });

        if (! $reserved) {
            return back()->withErrors(['download' => 'Ödeme henüz doğrulanmadı, sipariş geçersiz veya indirme limiti doldu.']);
        }

        return Storage::disk('local')->download(
            $filePath,
            Str::slug($order->product->title).'.'.pathinfo($filePath, PATHINFO_EXTENSION),
        );
    }
}
