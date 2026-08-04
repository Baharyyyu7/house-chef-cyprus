<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

final class RecoverAdminAccess extends Command
{
    protected $signature = 'admin:recover-access';

    protected $description = 'Interactively restore access for one existing, active production administrator.';

    public function handle(): int
    {
        if (! $this->input->isInteractive()) {
            $this->error('Bu komut yalnız interaktif terminalde çalıştırılabilir.');

            return self::FAILURE;
        }

        if (app()->environment('production') && ! $this->confirm('Production hesabı için erişim kurtarma işlemini onaylıyor musunuz?', false)) {
            $this->warn('İşlem iptal edildi.');

            return self::FAILURE;
        }

        $email = mb_strtolower(trim((string) $this->ask('Hedef e-posta adresi')));
        $user = User::query()->where('email', $email)->first();

        if ($user === null) {
            $this->error('Hedef kullanıcı bulunamadı; hiçbir kullanıcı oluşturulmadı.');

            return self::FAILURE;
        }

        if (in_array((string) $user->account_status, ['suspended', 'deleted'], true)) {
            $this->error('Askıya alınmış veya silinmiş hesap otomatik olarak etkinleştirilemez.');

            return self::FAILURE;
        }

        if ($user->email_verified_at === null && ! $this->confirm('E-posta doğrulamasını bu işlem kapsamında onaylıyor musunuz?', false)) {
            $this->error('Admin erişimi için e-posta doğrulaması açık onay gerektirir.');

            return self::FAILURE;
        }

        $password = (string) $this->secret('Yeni güçlü parola');
        $confirmation = (string) $this->secret('Yeni güçlü parolayı tekrar girin');
        $validator = Validator::make([
            'password' => $password,
            'password_confirmation' => $confirmation,
        ], [
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
        ]);

        if ($validator->fails()) {
            $this->error('Parola güvenlik kurallarını karşılamıyor.');

            return self::FAILURE;
        }

        $passwordHash = Hash::make($password);
        unset($password, $confirmation);

        DB::transaction(function () use ($user, $passwordHash): void {
            $user->forceFill([
                'password' => $passwordHash,
                'role' => 'admin',
                'email_verified_at' => $user->email_verified_at ?? now(),
                'remember_token' => Str::random(60),
            ])->save();

            if (DB::getSchemaBuilder()->hasTable('sessions')) {
                DB::table('sessions')->where('user_id', $user->id)->delete();
            }

            if (DB::getSchemaBuilder()->hasTable('password_reset_tokens')) {
                DB::table('password_reset_tokens')->where('email', $user->email)->delete();
            }

            AuditLog::query()->create([
                'user_id' => null,
                'action' => 'user.admin_access_recovered',
                'auditable_type' => User::class,
                'auditable_id' => $user->id,
                'metadata' => [
                    'user_public_id' => (string) $user->public_id,
                    'source' => 'interactive_console',
                    'sessions_revoked' => true,
                    'reset_tokens_cleared' => true,
                ],
            ]);
        });

        $this->info('Erişim kurtarma tamamlandı: admin rolü atandı, oturumlar kapatıldı ve sıfırlama tokenları temizlendi.');

        return self::SUCCESS;
    }
}
