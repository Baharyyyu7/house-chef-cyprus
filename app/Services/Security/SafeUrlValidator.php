<?php

declare(strict_types=1);

namespace App\Services\Security;

use Illuminate\Validation\ValidationException;

final class SafeUrlValidator
{
    /** @throws ValidationException */
    public function validate(string $url): string
    {
        $parts = parse_url($url);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower((string) ($parts['host'] ?? ''));
        $port = $parts['port'] ?? null;
        if (! in_array($scheme, ['http', 'https'], true) || $host === '' || isset($parts['user']) || isset($parts['pass']) || ($port !== null && ! in_array($port, [80, 443], true)) || $this->isPrivateHost($host)) {
            throw ValidationException::withMessages(['url' => 'Bu dış URL güvenlik politikası tarafından engellendi.']);
        }

        return $url;
    }

    private function isPrivateHost(string $host): bool
    {
        if (in_array($host, ['localhost', 'metadata.google.internal'], true) || str_ends_with($host, '.localhost')) {
            return true;
        }
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return ! filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
        }

        return false;
    }
}
