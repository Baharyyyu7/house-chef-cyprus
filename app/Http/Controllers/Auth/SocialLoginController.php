<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Events\MemberRegistered;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirect;
use Throwable;

/**
 * Google ve GitHub ile giriş.
 *
 * Sağlayıcı yapılandırılmamışsa rota 404 döner ve düğme zaten basılmaz:
 * anahtarsız bir düğme, tıklayan herkesi hata sayfasına götürür.
 */
final class SocialLoginController extends Controller
{
    public function redirect(string $provider): SymfonyRedirect|RedirectResponse
    {
        $this->saglayiciyiDogrula($provider);

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider): RedirectResponse
    {
        $this->saglayiciyiDogrula($provider);

        try {
            $hesap = Socialite::driver($provider)->user();
        } catch (Throwable) {
            // Kullanıcı izni reddettiğinde ya da jeton süresi dolduğunda buraya
            // düşülür; teknik ayrıntı kullanıcıya gösterilmez.
            return redirect()->route('login')->withErrors([
                'email' => 'Giriş tamamlanamadı. Lütfen tekrar deneyin veya e-posta ile giriş yapın.',
            ]);
        }

        $eposta = (string) $hesap->getEmail();

        if ($eposta === '') {
            // E-posta olmadan hesap eşleştirilemez: aynı kişinin ikinci kez
            // gelişinde onu tanıyamaz, her seferinde yeni hesap açardık.
            return redirect()->route('login')->withErrors([
                'email' => 'Bu hesapta doğrulanmış bir e-posta adresi görünmüyor. E-posta ile kayıt olabilirsiniz.',
            ]);
        }

        $kullanici = DB::transaction(function () use ($provider, $hesap, $eposta): User {
            // Önce sağlayıcı kimliğiyle aranır: kullanıcı Google'daki
            // e-postasını değiştirse bile aynı hesaba girmeye devam eder.
            $mevcut = User::query()
                ->where('social_provider', $provider)
                ->where('social_id', (string) $hesap->getId())
                ->first();

            if ($mevcut) {
                return $mevcut;
            }

            $epostaSahibi = User::query()->where('email', $eposta)->first();

            if ($epostaSahibi) {
                if ($epostaSahibi->isSuspended()) {
                    // Askıdaki hesaba sağlayıcı bağlanmaz; giriş aşağıda reddedilir.
                    return $epostaSahibi;
                }

                // Aynı e-postayla daha önce parolayla kaydolmuş biri: hesabı
                // bölmek yerine sağlayıcı kimliği mevcut hesaba bağlanır.
                $epostaSahibi->forceFill([
                    'social_provider' => $provider,
                    'social_id' => (string) $hesap->getId(),
                    'email_verified_at' => $epostaSahibi->email_verified_at ?? now(),
                ])->save();

                return $epostaSahibi;
            }

            return User::query()->create([
                'name' => (string) ($hesap->getName() ?: Str::before($eposta, '@')),
                'email' => $eposta,
                // Sosyal girişte parola yok. Rastgele bir dize koymak, kullanıcının
                // bilmediği bir parolayı varmış gibi göstermek olurdu.
                'password' => null,
                'email_verified_at' => now(),
                'social_provider' => $provider,
                'social_id' => (string) $hesap->getId(),
                'registration_source' => $provider,
                'terms_accepted_at' => now(),
            ]);
        });

        if ($kullanici->isSuspended()) {
            // Parola formundaki askı kuralı burada da geçerli: sosyal giriş
            // askıyı atlatan bir yan kapı olmamalı.
            return redirect()->route('login')->withErrors([
                'email' => 'Bu hesap geçici olarak askıya alındı.',
            ]);
        }

        if ($kullanici->wasRecentlyCreated) {
            try {
                event(new Registered($kullanici));
            } catch (Throwable $e) {
                Log::warning('Social login registration event failed: '.$e->getMessage());
            }

            try {
                event(new MemberRegistered($kullanici));
            } catch (Throwable $e) {
                Log::warning('Social login member registration event failed: '.$e->getMessage());
            }
        }

        Auth::login($kullanici, remember: true);
        request()->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    private function saglayiciyiDogrula(string $provider): void
    {
        abort_unless(
            (bool) config("social_login.providers.{$provider}.enabled"),
            404
        );
    }
}
