<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Contracts\Media\MediaStorageInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

final class LocalMediaStorage implements MediaStorageInterface
{
    public function storePublicImage(UploadedFile $file, string $directory = 'media'): array
    {
        $this->assertSafeImage($file);
        $extension = $file->extension();
        $path = $file->storeAs(trim($directory, '/'), bin2hex(random_bytes(16)).'.'.$extension, 'public');

        return ['disk' => 'public', 'path' => $path, 'mime_type' => (string) $file->getMimeType(), 'size' => (int) $file->getSize()];
    }

    private function assertSafeImage(UploadedFile $file): void
    {
        $mime = (string) $file->getMimeType();
        if (! $file->isValid() || ! in_array($mime, config('security.allowed_media_mimes'), true) || (int) $file->getSize() > (int) config('security.max_upload_bytes')) {
            throw ValidationException::withMessages(['file' => 'Dosya türü veya boyutu güvenlik politikasına uygun değil.']);
        }
        $dimensions = @getimagesize($file->getRealPath() ?: '');
        if ($dimensions === false || ((int) $dimensions[0] * (int) $dimensions[1]) > (int) config('security.max_image_pixels')) {
            throw ValidationException::withMessages(['file' => 'Görsel boyutları güvenlik politikasını aşıyor.']);
        }
    }
}
