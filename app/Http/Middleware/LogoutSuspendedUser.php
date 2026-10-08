<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Askıdaki hesabı her web isteğinde oturumdan çıkarır.
 *
 * Askı kontrolü yalnız parola formunda yapılsaydı yetmezdi: "beni hatırla"
 * çerezi, askıdan önce açılmış bir oturum ya da sosyal giriş formu hiç
 * görmeden kullanıcıyı içeri alır. Kapı burada, oturumun okunduğu yerde.
 */
final class LogoutSuspendedUser
{
    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next): Response
    {
        $kullanici = Auth::guard('web')->user();

        if ($kullanici === null || ! $kullanici->isSuspended()) {
            return $next($request);
        }

        Auth::guard('web')->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        $mesaj = 'Bu hesap geçici olarak askıya alındı.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $mesaj], 403);
        }

        return redirect()->route('login')->withErrors(['email' => $mesaj]);
    }
}
