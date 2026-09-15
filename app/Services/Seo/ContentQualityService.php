<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\BlogPost;

final class ContentQualityService
{
    /** @return array{passed:bool,score:int,issues:list<string>} */
    public function evaluate(BlogPost $post): array
    {
        $issues = [];
        if (blank($post->content_purpose)) {
            $issues[] = 'Özgün içerik amacı belirtilmemiş.';
        }
        if (blank($post->search_intent)) {
            $issues[] = 'Arama niyeti belirtilmemiş.';
        }
        if (blank($post->primary_keyword)) {
            $issues[] = 'Ana konu belirtilmemiş.';
        }
        if (blank($post->source_notes)) {
            $issues[] = 'Kaynak/fact-check notu gerekli.';
        }
        if ($post->recipes()->count() === 0) {
            $issues[] = 'En az bir ilgili, doğrulanmış tarif bağlantısı gerekli.';
        }
        if (blank($post->meta_title) || blank($post->meta_description)) {
            $issues[] = 'SEO başlığı ve açıklaması gerekli.';
        }
        if (! in_array($post->fact_check_status, ['passed', 'verified'], true)) {
            $issues[] = 'Fact-check tamamlanmamış.';
        }
        if (! in_array($post->originality_status, ['passed', 'clean', 'original'], true)) {
            $issues[] = 'Özgünlük denetimi tamamlanmamış.';
        }
        if (! in_array($post->editorial_status, ['approved', 'verified'], true)) {
            $issues[] = 'Editoryal onay gerekli.';
        }

        return ['passed' => $issues === [], 'score' => max(0, 100 - count($issues) * 11), 'issues' => $issues];
    }
}
