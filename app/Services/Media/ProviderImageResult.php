<?php

declare(strict_types=1);

namespace App\Services\Media;

final readonly class ProviderImageResult
{
    /** @param array<string,mixed> $diagnostic */
    public function __construct(
        public string $status,
        public ?string $errorCode,
        public ?string $safeMessage,
        public ?string $bytes = null,
        public ?string $mime = null,
        public ?string $providerRequestId = null,
        public array $diagnostic = [],
    ) {}

    public function hasImage(): bool
    {
        return $this->status === 'image_received' && $this->bytes !== null && $this->mime !== null;
    }
}
