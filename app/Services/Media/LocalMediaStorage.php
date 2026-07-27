<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Contracts\Media\MediaStorageInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

final class LocalMediaStorage implements MediaStorageInterface
{
    public function storePublicImage(UploadedFile $file, string $directory = 'media'): array
    {
        return $this->storeImage($file, $directory, 'public');
    }

    public function storeImage(UploadedFile $file, string $directory = 'media', string $visibility = 'public'): array
    {
        [$width, $height, $imageType, $mime] = $this->assertSafeImage($file);
        if (! in_array($visibility, ['public', 'private'], true)) {
            throw ValidationException::withMessages(['visibility' => 'Geçersiz medya görünürlüğü.']);
        }
        $disk = $visibility === 'public' ? 'public' : 'local';
        $directory = trim($directory, '/');
        if ($directory === '' || str_contains($directory, '..')) {
            throw ValidationException::withMessages(['file' => 'Geçersiz depolama dizini.']);
        }
        $extension = match ($imageType) {
            IMAGETYPE_JPEG => 'jpg',
            IMAGETYPE_PNG => 'png',
            IMAGETYPE_WEBP => 'webp',
            default => throw ValidationException::withMessages(['file' => 'Desteklenmeyen görsel türü.']),
        };
        $path = $directory.'/'.bin2hex(random_bytes(20)).'.'.$extension;
        $contents = $this->reencode($file->getRealPath() ?: '', $imageType, $width, $height);
        Storage::disk($disk)->put($path, $contents, ['visibility' => $visibility]);

        return ['disk' => $disk, 'path' => $path, 'mime_type' => $mime, 'size' => strlen($contents), 'width' => $width, 'height' => $height];
    }

    /** @return array{int,int,int,string} */
    private function assertSafeImage(UploadedFile $file): array
    {
        $mime = (string) $file->getMimeType();
        $name = $file->getClientOriginalName();
        $extension = strtolower((string) pathinfo($name, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        if (! $file->isValid() || ! in_array($mime, config('security.allowed_media_mimes'), true) || ! in_array($extension, $allowedExtensions, true) || substr_count($name, '.') > 1 || (int) $file->getSize() > (int) config('security.max_upload_bytes')) {
            throw ValidationException::withMessages(['file' => 'Dosya türü veya boyutu güvenlik politikasına uygun değil.']);
        }
        $dimensions = @getimagesize($file->getRealPath() ?: '');
        $imageType = is_array($dimensions) ? (int) $dimensions[2] : 0;
        $magicMime = is_array($dimensions) ? (string) $dimensions['mime'] : '';
        if ($dimensions === false || ! in_array($imageType, [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true) || $magicMime !== $mime || ((int) $dimensions[0] * (int) $dimensions[1]) > (int) config('security.max_image_pixels')) {
            throw ValidationException::withMessages(['file' => 'Görsel boyutları güvenlik politikasını aşıyor.']);
        }

        return [(int) $dimensions[0], (int) $dimensions[1], $imageType, $magicMime];
    }

    private function reencode(string $path, int $imageType, int $width, int $height): string
    {
        $contents = @file_get_contents($path);
        $source = $contents === false ? false : @imagecreatefromstring($contents);
        if ($source === false) {
            throw ValidationException::withMessages(['file' => 'Görsel güvenli biçimde işlenemedi.']);
        }
        if ($imageType === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
            $exif = @exif_read_data($path);
            $orientation = is_array($exif) ? (int) ($exif['Orientation'] ?? 1) : 1;
            if ($orientation === 3) {
                $source = imagerotate($source, 180, 0);
            }
            if ($orientation === 6) {
                $source = imagerotate($source, -90, 0);
                [$width, $height] = [$height, $width];
            }
            if ($orientation === 8) {
                $source = imagerotate($source, 90, 0);
                [$width, $height] = [$height, $width];
            }
        }
        ob_start();
        $result = match ($imageType) {
            IMAGETYPE_JPEG => imagejpeg($source, null, 88),
            IMAGETYPE_PNG => imagepng($source, null, 6),
            IMAGETYPE_WEBP => function_exists('imagewebp') ? imagewebp($source, null, 88) : false,
            default => false,
        };
        $encoded = ob_get_clean();
        imagedestroy($source);
        if (! $result || $encoded === '') {
            throw ValidationException::withMessages(['file' => 'Görsel güvenli biçimde dönüştürülemedi.']);
        }

        return $encoded;
    }
}
