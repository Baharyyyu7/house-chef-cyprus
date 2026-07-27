<?php

declare(strict_types=1);

namespace App\Contracts\Media;

use Illuminate\Http\UploadedFile;

interface MediaStorageInterface
{
    /** @return array{disk:string,path:string,mime_type:string,size:int} */
    public function storePublicImage(UploadedFile $file, string $directory = 'media'): array;
}
