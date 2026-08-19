<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateMediaThumbnailsJob;
use App\Models\AuditLog;
use App\Models\BlogPost;
use App\Models\Ingredient;
use App\Models\Media;
use App\Models\Recipe;
use App\Services\Media\LocalMediaStorage;
use App\Services\Media\PublicMediaEligibilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Output\BufferedOutput;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class MediaController extends Controller
{
    public function index(Request $request, PublicMediaEligibilityService $gate): View
    {
        $this->admin();

        // İçe aktarılmış ama insan onayı bekleyen görseller listenin içinde
        // kayboluyordu; sonuç olarak dosyası ve varyantları hazır fotoğraflar
        // hiç yayına çıkmadı. Onay bekleyenler ayrı bir bölümde toplanır.
        $onayBekleyen = Media::query()
            ->with('mediable')
            ->where('media_status', 'approved_real_media')
            ->whereNull('approved_by_user_id')
            ->whereNotNull('path')
            ->where('path', '!=', '')
            // Yer tutucu kapaklar onay kuyruğunda işi yok: bunlar sistemin
            // fotoğraf olmadığında kendi çizdiği tipografik kapaklar, insan
            // onayı bekleyen çekimler değil. Kuyruğu 3212 tanesi dolduruyor
            // ve gerçek yemek fotoğrafları arada kayboluyordu.
            ->where('path', 'not like', 'fallbacks/%')
            ->latest()
            ->limit(60)
            ->get()
            ->filter(fn (Media $item): bool => $gate->reason($item) === 'human_approval_missing')
            // path sütununun dolu olması dosyanın durduğu anlamına gelmiyor.
            // Kuyruktaki altı kaydın da kaynak dosyası diskte yoktu: onay
            // ekranı gri kutular gösteriyordu ve onaylayan kişi görmediği bir
            // şeyi onaylamak zorunda kalıyordu.
            ->filter(fn (Media $item): bool => Storage::disk($item->disk)->exists($item->path))
            ->values();

        // Liste 7178 satırı 240 sayfaya yayıyordu ve sütunları public_id, disk,
        // MIME gibi bir kararı desteklemeyen alanlardı. Bu satırların 2891'i
        // dosyası olmayan ölü kayıt; varsayılan görünüm artık dosyası olanı
        // gösterir, ölü kayıtlar kendi süzgecinden çağrılır.
        $sayilar = [
            'yayinda' => Media::query()->where('media_status', 'approved_real_media')->whereNotNull('approved_by_user_id')->count(),
            'onay_bekleyen' => $onayBekleyen->count(),
            'dosyasiz' => Media::query()->where(fn ($q) => $q->whereNull('path')->orWhere('path', ''))->count(),
            'tumu' => Media::query()->count(),
        ];

        $suzgec = $request->string('durum')->toString() ?: 'dosyali';
        $arama = trim($request->string('q')->toString());

        $media = Media::query()
            ->with('mediable')
            // Sekme, üstteki onay bölümüyle aynı kayıtları göstermeli. Ayrı
            // sorgular yazıldığında sekme, dosyası olmayan kayıtları geri
            // getiriyordu: aynı sayfada iki farklı "onay bekleyen" tanımı vardı.
            ->when($suzgec === 'onay', fn ($q) => $q->whereIn('id', $onayBekleyen->pluck('id')->all()))
            ->when($suzgec === 'dosyali', fn ($q) => $q->whereNotNull('path')->where('path', '!=', ''))
            ->when($suzgec === 'yayinda', fn ($q) => $q->where('media_status', 'approved_real_media')->whereNotNull('approved_by_user_id'))
            ->when($suzgec === 'dosyasiz', fn ($q) => $q->where(fn ($inner) => $inner->whereNull('path')->orWhere('path', '')))
            ->when($arama !== '', function ($q) use ($arama): void {
                // Bir görseli arayan kişi onu bağlı olduğu yemeğin adıyla arar,
                // ULID'siyle değil.
                $q->where(fn ($inner) => $inner
                    ->where('alt_text', 'like', '%'.$arama.'%')
                    ->orWhereHasMorph('mediable', [Recipe::class, BlogPost::class], fn ($m) => $m->where('title', 'like', '%'.$arama.'%')));
            })
            ->latest()
            ->paginate(24)
            ->withQueryString();

        return view('admin.media.index', compact('media', 'onayBekleyen', 'sayilar', 'suzgec', 'arama'));
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

    /**
     * İçe aktarılan görseller yayına çıkamıyordu. PublicMediaEligibilityService
     * gerçek bir kullanıcıya bağlı inceleme ve onay kaydı arıyor -- ki bu doğru
     * bir kural, hiçbir otomatik akış tek başına görsel yayınlayamamalı. Ama
     * yönetim panelinde bu onayı verecek bir ekran yoktu: dosya içeri alınıyor,
     * varyantları üretiliyor ve orada kalıyordu.
     *
     * Onay yalnız başka her koşulu geçmiş görsellere verilebilir; eksik olan
     * tek şey insan kararıysa bu ekran onu kaydeder.
     */
    public function approve(string $publicId, PublicMediaEligibilityService $gate): RedirectResponse
    {
        $admin = $this->admin();
        $media = Media::query()->where('public_id', $publicId)->firstOrFail();

        if ($gate->reason($media) === null) {
            return back()->with('status', 'Bu görsel zaten yayında.');
        }

        // Kapı insan onayını varyant ve kaynak kontrollerinden önce sorguluyor;
        // "eksik olan yalnız onay" sonucuna bakmak yetmez, çünkü arkasındaki
        // kontroller hiç çalışmamış olabilir. Onay bellekte uygulanır ve geriye
        // başka engel kalıp kalmadığı ölçülür.
        $media->forceFill([
            'media_reviewer_user_id' => $admin->id,
            'media_reviewed_at' => now(),
            'approved_by_user_id' => $admin->id,
            'approved_at' => now(),
        ]);

        $kalan = $gate->reason($media);

        if ($kalan !== null) {
            return back()->with('error', 'Görsel onaya hazır değil: '.$kalan);
        }

        $media->save();

        $this->audit('media.approve', $media);

        return back()->with('status', 'Görsel onaylandı ve yayına alındı.');
    }

    /**
     * Tek tek onaylamak 40 bekleyen görsel için 40 tıklama demekti ve üretim
     * sürdükçe bu sayı 200'ü geçiyor. Toplu onay, kararın kendisini otomatikleş-
     * tirmez: editör ızgaraya bakar, göstermek istediklerini işaretler ve bir
     * kez onaylar. Her kayıt yine tek tek kapıdan geçirilir; geçemeyen atlanır
     * ve kaç tanesinin neden atlandığı bildirilir.
     */
    public function approveMany(Request $request, PublicMediaEligibilityService $gate): RedirectResponse
    {
        $admin = $this->admin();

        $secilenler = $request->validate([
            'public_ids' => ['required', 'array', 'max:200'],
            'public_ids.*' => ['string', 'size:26'],
        ])['public_ids'];

        $onaylanan = 0;
        $atlanan = 0;

        foreach (Media::query()->whereIn('public_id', $secilenler)->get() as $media) {
            if ($gate->reason($media) === null) {
                continue;
            }

            $media->forceFill([
                'media_reviewer_user_id' => $admin->id,
                'media_reviewed_at' => now(),
                'approved_by_user_id' => $admin->id,
                'approved_at' => now(),
            ]);

            if ($gate->reason($media) !== null) {
                $atlanan++;

                continue;
            }

            $media->save();
            $this->audit('media.approve', $media);
            $onaylanan++;
        }

        return back()->with('status', $atlanan === 0
            ? $onaylanan.' görsel onaylandı ve yayına alındı.'
            : $onaylanan.' görsel onaylandı, '.$atlanan.' tanesi başka bir kontrolü geçemediği için atlandı.');
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

    /**
     * Teslim edilen görselleri onay kuyruğuna alır.
     *
     * Aynı iş SSH ile tek komuttan çalıştırılıyordu; onayı verecek kişi
     * terminale girmek zorunda kalıyordu. Düğme aynı komutu çağırır ve
     * sonucunu ekrana yazar - hiçbir görsel yayına çıkmaz, hepsi onay
     * kuyruğunda durur.
     */
    public function intake(): RedirectResponse
    {
        $this->admin();

        $cikti = new BufferedOutput();
        Artisan::call('media:intake', ['--apply' => true], $cikti);

        // Komut kendi özetini yazıyor; kullanıcıya sayıyı gösteren satırı
        // buluyoruz ki ne olduğunu terminale bakmadan görsün.
        $ozet = collect(explode("\n", $cikti->fetch()))
            ->map(static fn (string $satir): string => trim(strip_tags($satir)))
            ->first(static fn (string $satir): bool => str_contains($satir, 'onay kuyruğuna alındı'));

        $this->audit('media.intake');

        return back()->with('status', $ozet ?: 'İçe aktarma çalıştı; onay kuyruğunu kontrol edin.');
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

    private function admin(): \App\Models\User
    {
        $this->authorizeAdminOperations();

        $user = auth()->user();
        abort_unless($user instanceof \App\Models\User, 403);

        return $user;
    }
}
