<?php

declare(strict_types=1);

namespace App\Contracts\Media;

interface ImageGenerationProviderInterface
{
    /** @return array{name:string,configured:bool,enabled:bool,image_capability:string,model:?string,safe_message:string} */
    public function health(bool $live = false): array;
}
