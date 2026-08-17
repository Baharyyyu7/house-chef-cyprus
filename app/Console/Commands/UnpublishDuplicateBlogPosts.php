<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\BlogPost;
use Illuminate\Console\Command;

/**
 * Yayındaki 216 yazının 180'i aynı başlığı taşıyor: "Kusursuz Rehberler
 * Sırları ve Profesyonel Mutfak İpuçları". Gövdeleri de tek şablondan
 * üretilmiş; yalnız mutfak adı ve bir numara değişiyor. Kalanların bir kısmı
 * da ikişer kez yayınlanmış.
 *
 * Kural başlığa göredir: aynı başlığı taşıyan yazılardan yalnız biri yayında
 * kalır. Hangisinin kalacağı gövde uzunluğuna göre seçilir -- en dolu olan --
 * eşitlikte en eski tarihli olan. Kayıt silinmez, taslağa çekilir.
 */
final class UnpublishDuplicateBlogPosts extends Command
{
    protected $signature = 'blog:unpublish-duplicates {--apply : Değişikliği yaz, aksi hâlde yalnız rapor}';

    protected $description = 'Aynı başlığı paylaşan yayındaki yazılardan biri hariç hepsini taslağa çeker.';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');

        $gruplar = BlogPost::query()
            ->where('status', 'published')
            ->get(['id', 'title', 'body', 'published_at'])
            ->groupBy(fn (BlogPost $post): string => mb_strtolower(trim((string) $post->title), 'UTF-8'))
            ->filter(fn ($grup): bool => $grup->count() > 1);

        if ($gruplar->isEmpty()) {
            $this->info('Aynı başlığı paylaşan yayındaki yazı yok.');

            return self::SUCCESS;
        }

        $cekilecek = collect();

        foreach ($gruplar as $grup) {
            // En dolu gövde kalır; eşitlikte en eski yayın tarihi.
            $kalan = $grup
                ->sortByDesc(fn (BlogPost $post): int => mb_strlen(strip_tags((string) $post->body)))
                ->sortBy(fn (BlogPost $post) => $post->published_at, SORT_REGULAR)
                ->sortByDesc(fn (BlogPost $post): int => mb_strlen(strip_tags((string) $post->body)))
                ->first();

            $cekilecek = $cekilecek->merge($grup->reject(fn (BlogPost $post): bool => $post->id === $kalan->id));
        }

        $this->line(sprintf('  aynı başlıklı grup : %d', $gruplar->count()));
        $this->line(sprintf('  taslağa çekilecek  : %d', $cekilecek->count()));
        $this->newLine();
        $this->line('  en kalabalık gruplar:');

        foreach ($gruplar->sortByDesc(fn ($g) => $g->count())->take(4) as $grup) {
            $this->line(sprintf('    %3dx  %s', $grup->count(), mb_substr((string) $grup->first()->title, 0, 58)));
        }

        if (! $apply) {
            $this->newLine();
            $this->comment('Uygulamak için --apply ekleyin. Her başlıktan bir yazı yayında kalır, kalanlar silinmez.');

            return self::SUCCESS;
        }

        foreach ($cekilecek as $post) {
            BlogPost::query()->whereKey($post->id)->update(['status' => 'draft']);
        }

        $kalanSayi = BlogPost::query()->where('status', 'published')->count();

        $this->newLine();
        $this->info(sprintf('%d yazı taslağa çekildi. Yayında %d yazı kaldı.', $cekilecek->count(), $kalanSayi));

        return self::SUCCESS;
    }
}
