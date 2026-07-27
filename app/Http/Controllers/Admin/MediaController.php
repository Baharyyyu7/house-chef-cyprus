<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateMediaThumbnailsJob;
use App\Models\AuditLog;
use App\Models\Ingredient;
use App\Models\Media;
use App\Models\Recipe;
use App\Services\Media\LocalMediaStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class MediaController extends Controller
{
    public function index(Request $request): View
    {
        $this->admin();
        $media = Media::query()
            ->with('mediable')
            ->when($request->string('kind')->isNotEmpty(), fn ($q) => $q->where('kind', $request->string('kind')->toString()))
            ->when($request->string('disk')->isNotEmpty(), fn ($q) => $q->where('disk', $request->string('disk')->toString()))
            ->when($request->string('visibility')->isNotEmpty(), fn ($q) => $q->where('visibility', $request->string('visibility')->toString()))
            ->when($request->string('status')->isNotEmpty(), fn ($q) => $q->where('processing_status', $request->string('status')->toString()))
            ->latest()->paginate(30)->withQueryString();

        return view('admin.media.index', compact('media'));
    }

    public function create(): View
    {
        $this->admin();

        return view('admin.media.create');
    }

    public function store(Request $request, LocalMediaStorage $storage): RedirectResponse
    {
        $this->admin();
        $data = $request->validate([
            'file' => ['required', 'file', 'max:'.max(1, (int) config('security.max_upload_bytes') / 1024)],
            'entity_type' => ['required', Rule::in(['recipe', 'ingredient'])],
            'entity_public_id' => ['required', 'string', 'size:26'],
            'visibility' => ['required', Rule::in(['public', 'private'])],
            'alt_text' => ['nullable', 'string', 'max:255'],
        ]);
        $entity = $this->entity((string) $data['entity_type'], (string) $data['entity_public_id']);
        abort_if($entity === null, 422, 'İlişkili kayıt bulunamadı.');
        $stored = $storage->storeImage($request->file('file'), 'media/images', (string) $data['visibility']);
        $media = $entity->media()->create([
            'kind' => 'image', 'disk' => $stored['disk'], 'path' => $stored['path'], 'mime_type' => $stored['mime_type'], 'size' => $stored['size'],
            'visibility' => $data['visibility'], 'alt_text' => $data['alt_text'] ?? null, 'width' => $stored['width'], 'height' => $stored['height'],
            'processing_status' => 'pending', 'thumbnail_status' => 'pending',
        ]);
        GenerateMediaThumbnailsJob::dispatch($media->public_id);
        $this->audit('media.upload', $media, ['visibility' => $media->visibility]);

        return to_route('admin.media.show', $media->public_id)->with('status', 'Görsel güvenli olarak yüklendi; küçük görseller kuyruğa alındı.');
    }

    public function show(string $publicId): View
    {
        $this->admin();
        $media = Media::query()->with('mediable')->where('public_id', $publicId)->firstOrFail();

        return view('admin.media.show', compact('media'));
    }

    public function downloadPrivate(string $publicId): StreamedResponse
    {
        $this->admin();
        $media = Media::query()->where('public_id', $publicId)->where('visibility', 'private')->firstOrFail();
        abort_unless(is_string($media->path) && str_starts_with($media->path, 'media/') && ! str_contains($media->path, '..') && Storage::disk($media->disk)->exists($media->path), 404);

        return Storage::disk($media->disk)->download($media->path, 'media-'.$media->public_id);
    }

    public function regenerate(string $publicId): RedirectResponse
    {
        $this->admin();
        $media = Media::query()->where('public_id', $publicId)->firstOrFail();
        GenerateMediaThumbnailsJob::dispatch($media->public_id);
        $this->audit('media.thumbnails.regenerate', $media);

        return back()->with('status', 'Thumbnail üretimi güvenli olarak kuyruğa alındı.');
    }

    public function destroy(string $publicId): RedirectResponse
    {
        $this->admin();
        $media = Media::query()->where('public_id', $publicId)->firstOrFail();
        $this->deleteMediaFiles($media);
        $this->audit('media.delete', $media);
        $media->delete();

        return to_route('admin.media.index')->with('status', 'Medya ve bağlı küçük görseller güvenli biçimde silindi.');
    }

    public function scanOrphans(): RedirectResponse
    {
        $this->admin();
        Artisan::call('media:scan-orphans');
        $this->audit('media.orphan_scan');

        return back()->with('status', 'Orphan taraması dry-run olarak tamamlandı.');
    }

    public function cleanupTemporary(): RedirectResponse
    {
        $this->admin();
        Artisan::call('media:cleanup-temporary');
        $this->audit('media.temporary_cleanup');

        return back()->with('status', 'Geçici dosya temizliği dry-run olarak tamamlandı.');
    }

    private function entity(string $type, string $publicId): Recipe|Ingredient|null
    {
        return $type === 'recipe'
            ? Recipe::query()->where('public_id', $publicId)->first()
            : Ingredient::query()->where('public_id', $publicId)->first();
    }

    private function deleteMediaFiles(Media $media): void
    {
        $disk = Storage::disk($media->disk);
        foreach (array_filter(array_merge([$media->path], $media->thumbnail_paths ?? [])) as $path) {
            if (str_starts_with($path, 'media/') && ! str_contains($path, '..')) {
                $disk->delete($path);
            }
        }
    }

    private function audit(string $action, ?Media $media = null, array $metadata = []): void
    {
        AuditLog::create(['user_id' => auth()->id(), 'action' => $action, 'auditable_type' => $media?->getMorphClass(), 'auditable_id' => $media?->getKey(), 'metadata' => $metadata]);
    }

    private function admin(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }
}
