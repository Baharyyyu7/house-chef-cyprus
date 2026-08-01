<?php

declare(strict_types=1);

namespace App\Contracts\AI;

interface AiKitchenProviderInterface
{
    public function name(): string;

    /** @return array<string, scalar|bool|null> */
    public function health(): array;

    /** @param array<string, mixed> $request @return array{status:string,text:string,input_tokens:int,output_tokens:int,cost_cents:int,safe_error_code:?string} */
    public function respond(array $request): array;
}
