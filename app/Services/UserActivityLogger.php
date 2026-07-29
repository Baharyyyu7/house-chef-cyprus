<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class UserActivityLogger
{
    /** @param array<string,scalar|null> $metadata */
    public function record(User $user, string $eventType, ?Request $request = null, array $metadata = [], string $status = 'success'): void
    {
        $ip = $request?->ip();
        UserActivity::query()->create([
            'public_id' => (string) Str::ulid(),
            'user_id' => $user->id,
            'event_type' => $eventType,
            'route_name' => $request?->route()?->getName(),
            'ip_masked' => $this->maskForDisplay($ip),
            'ip_hash' => $ip === null ? null : hash_hmac('sha256', $ip, (string) config('app.key')),
            'device_summary' => $this->deviceSummary($request?->userAgent()),
            'status' => $status,
            'metadata' => array_filter($metadata, static fn (mixed $value, string $key): bool => ! in_array(strtolower($key), ['password', 'token', 'authorization', 'cookie', 'session'], true), ARRAY_FILTER_USE_BOTH),
            'occurred_at' => now(),
        ]);
        $user->forceFill(['last_activity_at' => now()])->saveQuietly();
    }

    public function maskForDisplay(?string $ip): ?string
    {
        if ($ip === null || $ip === '') {
            return null;
        }
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $ip);

            return $parts[0].'.'.$parts[1].'.xxx.xxx';
        }

        return substr($ip, 0, 8).'…';
    }

    private function deviceSummary(?string $userAgent): ?string
    {
        if ($userAgent === null || $userAgent === '') {
            return null;
        }

        return Str::limit(preg_replace('/[\r\n]+/', ' ', $userAgent) ?: '', 120, '');
    }
}
