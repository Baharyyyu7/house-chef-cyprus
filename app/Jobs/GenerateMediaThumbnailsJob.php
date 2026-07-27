<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Media;
use App\Services\Media\ThumbnailGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

final class GenerateMediaThumbnailsJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 90;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [15, 60, 180];

    public function __construct(public readonly string $mediaPublicId)
    {
        $this->onQueue('media')->afterCommit();
    }

    public function uniqueId(): string
    {
        return 'media-thumbnail:'.$this->mediaPublicId;
    }

    public function handle(ThumbnailGenerator $generator): void
    {
        $media = Media::query()->where('public_id', $this->mediaPublicId)->first();
        if ($media === null || $media->path === null) {
            return;
        }
        $media->update(['thumbnail_status' => 'processing', 'processing_status' => 'processing', 'processing_error_code' => null, 'processing_error_message' => null]);
        $paths = $generator->generate($media);
        $media->update(['thumbnail_paths' => $paths, 'thumbnail_status' => 'ready', 'processing_status' => 'ready', 'processed_at' => now()]);
    }

    public function failed(Throwable $exception): void
    {
        Media::query()->where('public_id', $this->mediaPublicId)->update([
            'thumbnail_status' => 'failed',
            'processing_status' => 'failed',
            'processing_error_code' => 'MEDIA_THUMBNAIL_FAILED',
            'processing_error_message' => 'Küçük görseller güvenli biçimde üretilemedi.',
        ]);
    }
}
