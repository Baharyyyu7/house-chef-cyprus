<?php

declare(strict_types=1);

namespace App\Services\Seo;

class PublicSourceResolver
{
    /** @return list<string> */
    public function resolve(string $host): array
    {
        $literal = trim($host, '[]');
        $addresses = filter_var($literal, FILTER_VALIDATE_IP) ? [$literal] : (gethostbynamel($host) ?: []);
        // Reject mixed public/private answers as well as private literals before any HTTP request.
        foreach ($addresses as $address) {
            if (! filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_GLOBAL_RANGE)) {
                return [];
            }
        }

        return array_values(array_unique($addresses));
    }
}
