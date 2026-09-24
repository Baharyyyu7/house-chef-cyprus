<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Models\ContentRevision;
use Illuminate\Console\Command;

/**
 * Derinleştirilmiş blog içerikleri resources/editorial/blog-content altında
 * sürüm kontrolünde durur; bu komut onları veritabanına uygular. Dry-run
 * varsayılandır ve yalnız dosyası olan yazılara dokunur.
 */
final class ApplyEditorialBlogContent extends Command
{
    private const DELIVERY_REVISION_REASON = 'Teslim edilen editoryal güncelleme';

    protected $signature = 'blog:apply-editorial-content
        {--apply : Değişiklikleri veritabanına yaz}
        {--slug= : Yalnız tek bir yazıyı uygula}
        {--json : Makine okunabilir çıktı}';

    protected $description = 'Güvenilir editoryal içerikleri mevcut yazılara uygular, teslimden gelen yenileri taslak açar; dry-run varsayılandır.';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $only = (string) ($this->option('slug') ?? '');
        // İki kaynak, iki ayrı güven seviyesi.
        //
        // Depodaki dosyalar PHP ve gözden geçirilmiş sürümün parçası; require
        // edilebilirler. Sunucudaki teslim klasörüne ise ajanlar yazıyor ve
        // oradan PHP require etmek doğrudan uzaktan kod çalıştırmaktır: o
        // klasöre dosya bırakabilen herkes üretimde istediği kodu koşturur.
        // Teslim klasörü yalnız JSON okur ve JSON çalıştırılamaz.
        $depoDizini = resource_path('editorial/blog-content');
        $teslimDizini = storage_path('app/content-import/blog');

        $applied = [];
        $olusturulan = [];
        $incelemeRevizyonlari = [];
        $unchanged = [];

        /** @var array<string, array{yol: string, tur: string}> $dosyalar */
        $dosyalar = [];

        if (is_dir($depoDizini)) {
            foreach (glob($depoDizini.'/*.php') ?: [] as $bulunan) {
                $dosyalar[basename($bulunan, '.php')] = ['yol' => $bulunan, 'tur' => 'php'];
            }
        }

        if (is_dir($teslimDizini)) {
            // Yalnız .json. Buraya bırakılan .php dosyası okunmaz, çalıştırılmaz
            // ve içeri alınmaz; sessizce görmezden gelinmesi yetmediği için
            // ayrıca raporlanır.
            foreach (glob($teslimDizini.'/*.php') ?: [] as $reddedilen) {
                $this->warn('Yok sayıldı (teslim klasöründe PHP çalıştırılmaz): '.basename($reddedilen));
            }

            foreach (glob($teslimDizini.'/*.json') ?: [] as $bulunan) {
                // Aynı slug iki yerde varsa teslim klasörü kazanır: yeni olan odur.
                $dosyalar[basename($bulunan, '.json')] = ['yol' => $bulunan, 'tur' => 'json'];
            }
        }

        if ($dosyalar === [] && ! is_dir($depoDizini) && ! is_dir($teslimDizini)) {
            $this->error('İçerik dizini yok: '.$depoDizini);

            return self::FAILURE;
        }

        foreach ($dosyalar as $slug => $kayit) {
            $file = $kayit['yol'];

            if ($only !== '' && $slug !== $only) {
                continue;
            }

            /** @var array<string, mixed> $payload */
            $payload = $kayit['tur'] === 'php'
                ? require $file
                : json_decode((string) file_get_contents($file), true);

            if (! is_array($payload)) {
                $this->warn('Okunamadı, atlandı: '.basename($file));

                continue;
            }
            $post = BlogPost::query()->where('slug', $slug)->first();

            if ($post === null) {
                // Yeni yazı taslak olarak açılır. Yayına çıkarmak ayrı ve
                // insana ait bir karardır: bir kez ~3200 makine üretimi sayfa
                // toplu yayına çıktı ve arama sonuçlarını doldurdu. O yüzden
                // bu komut hiçbir koşulda yayın durumuna dokunmaz.
                if (! $apply) {
                    $olusturulan[] = $slug;

                    continue;
                }

                $post = BlogPost::query()->create([
                    'slug' => $slug,
                    'title' => (string) ($payload['title'] ?? $slug),
                    'excerpt' => (string) ($payload['excerpt'] ?? ''),
                    'body' => (string) ($payload['body'] ?? ''),
                    'status' => 'draft',
                    'published_at' => null,
                ]);

                $olusturulan[] = $slug;
            }

            $changes = array_filter([
                'title' => $payload['title'] ?? null,
                'excerpt' => $payload['excerpt'] ?? null,
                'body' => $payload['body'] ?? null,
                'meta_title' => $payload['meta_title'] ?? null,
                'meta_description' => $payload['meta_description'] ?? null,
                'primary_keyword' => $payload['primary_keyword'] ?? null,
                'secondary_keywords' => $payload['secondary_keywords'] ?? null,
                'faq_blocks' => $payload['faq'] ?? null,
            ], static fn (mixed $value): bool => $value !== null);

            $before = mb_strlen((string) $post->body);
            $after = mb_strlen((string) ($changes['body'] ?? $post->body));

            if (in_array($post->status, ['published', 'scheduled'], true)) {
                if ($apply) {
                    $revision = ContentRevision::query()->firstOrNew([
                        'blog_post_id' => $post->id,
                        'reason' => self::DELIVERY_REVISION_REASON,
                    ]);

                    $revision->fill([
                        'revision_number' => $revision->exists ? $revision->revision_number : ((int) $post->revisions()->max('revision_number')) + 1,
                        'previous_body' => $post->body,
                        'proposed_body' => (string) ($changes['body'] ?? $post->body),
                        'changed_seo_fields' => $changes,
                        'created_by' => null,
                        'reviewed_at' => null,
                    ])->save();
                }

                $incelemeRevizyonlari[] = $slug;
                $applied[] = ['slug' => $slug, 'before' => $before, 'after' => $after, 'faq' => count($payload['faq'] ?? [])];

                continue;
            }

            if ($apply) {
                $post->forceFill([...$changes, 'last_refreshed_at' => now()])->save();
            }

            if ($before === $after && ! $apply) {
                $unchanged[] = $slug;
            }

            $applied[] = ['slug' => $slug, 'before' => $before, 'after' => $after, 'faq' => count($payload['faq'] ?? [])];
        }

        $report = [
            'mode' => $apply ? 'apply' : 'dry-run',
            'processed' => count($applied),
            'created_drafts' => $olusturulan,
            'review_revisions' => $incelemeRevizyonlari,
            'posts' => $applied,
        ];

        if ($this->option('json')) {
            $this->line((string) json_encode($report, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        foreach ($applied as $row) {
            $this->line(sprintf('  %-46s %5d → %5d karakter · %d SSS', $row['slug'], $row['before'], $row['after'], $row['faq']));
        }

        $this->info(sprintf('[%s] işlenen: %d · yeni taslak: %d', $report['mode'], count($applied), count($olusturulan)));

        if ($olusturulan !== []) {
            $this->warn(count($olusturulan).' yazı TASLAK olarak açıldı. Hiçbiri yayında değil; yayın kararı size ait.');
        }

        if ($incelemeRevizyonlari !== []) {
            $this->warn(count($incelemeRevizyonlari).' yayımlı veya planlı yazı için özel inceleme revizyonu oluşturuldu. Canlı içerik değiştirilmedi.');
        }

        if (! $apply && $applied !== []) {
            $this->warn('Yazmak için --apply ile tekrar çalıştırın.');
        }

        return self::SUCCESS;
    }
}
