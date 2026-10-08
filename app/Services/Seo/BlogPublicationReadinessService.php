<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\BlogPost;
use App\Models\Recipe;

final class BlogPublicationReadinessService
{
    public function __construct(
        private readonly ContentQualityService $quality,
        private readonly BlogOriginalityService $originality,
    ) {}

    /** @return array{passed:bool,score:int,issues:list<string>,metrics:array<string,int|bool>} */
    public function evaluate(BlogPost $post): array
    {
        $base = $this->quality->evaluate($post);
        $issues = $base['issues'];
        $plain = $post->okunanMetin();
        preg_match_all('/[\p{L}\p{N}]+/u', $plain, $words);
        $wordCount = count($words[0]);
        $secondaryCount = count($post->secondary_keywords ?? []);
        $faqCount = count($post->faq_blocks ?? []);
        $headingCount = preg_match_all('/^##\s+\S+/mu', (string) $post->body);
        $metaTitleLength = mb_strlen((string) $post->meta_title);
        $metaDescriptionLength = mb_strlen((string) $post->meta_description);

        if ($wordCount < 900) {
            $issues[] = 'İçerik en az 900 kelime olmalıdır.';
        }
        if ($secondaryCount < 3 || $secondaryCount > 6) {
            $issues[] = 'Üç ila altı ikincil anahtar kelime gerekli.';
        }
        if ($faqCount < 3 || $faqCount > 6) {
            $issues[] = 'Üç ila altı gerçek SSS gerekli.';
        }
        if ($headingCount < 2) {
            $issues[] = 'En az iki ikinci seviye başlık gerekli.';
        }
        if ($metaTitleLength === 0 || $metaTitleLength > 60) {
            $issues[] = 'SEO başlığı 1-60 karakter olmalıdır.';
        }
        if ($metaDescriptionLength < 140 || $metaDescriptionLength > 158) {
            $issues[] = 'SEO açıklaması 140-158 karakter olmalıdır.';
        }
        $kusurlar = $this->originality->govdeKusurlari((string) $post->body);
        if (in_array('ham_html', $kusurlar, true)) {
            $issues[] = 'Gövde ham HTML; sayfada boş basılır, önce Markdown\'a çevrilmeli.';
        }
        if (in_array('cumle_tekrari', $kusurlar, true)) {
            $issues[] = 'Aynı cümle yazı içinde tekrar ediyor.';
        }
        if (in_array('yer_tutucu', $kusurlar, true)) {
            $issues[] = 'Gövdede yer tutucu metin ya da bağlantı var.';
        }
        foreach ($this->kirikIcBaglantilar((string) $post->body) as $adres) {
            $issues[] = 'Kırık iç bağlantı: '.$adres;
        }
        if (blank($post->human_approved_at) || blank($post->human_approved_by_user_id)) {
            $issues[] = 'İnsan yayın onayı kaydı gerekli.';
        }

        $issues = array_values(array_unique($issues));

        return [
            'passed' => $issues === [],
            'score' => max(0, 100 - count($issues) * 8),
            'issues' => $issues,
            'metrics' => [
                'word_count' => $wordCount,
                'secondary_keyword_count' => $secondaryCount,
                'faq_count' => $faqCount,
                'heading_count' => $headingCount,
                'meta_title_length' => $metaTitleLength,
                'meta_description_length' => $metaDescriptionLength,
                'human_approved' => filled($post->human_approved_at) && filled($post->human_approved_by_user_id),
            ],
        ];
    }

    /**
     * Gövdedeki tarif ve yazı bağlantılarından yayında karşılığı olmayanlar.
     *
     * @return list<string>
     */
    private function kirikIcBaglantilar(string $govde): array
    {
        preg_match_all('#\]\((?:https?://(?:www\.)?housechefcyprus\.com)?/(tarifler|blog)/([a-z0-9-]+)/?\)#i', $govde, $eslesmeler, PREG_SET_ORDER);
        $kirik = [];

        foreach ($eslesmeler as [, $tur, $slug]) {
            $model = strtolower($tur) === 'blog' ? BlogPost::class : Recipe::class;
            if (! $model::query()->where('slug', $slug)->where('status', 'published')->exists()) {
                $kirik[] = '/'.strtolower($tur).'/'.$slug;
            }
        }

        return array_values(array_unique($kirik));
    }
}
