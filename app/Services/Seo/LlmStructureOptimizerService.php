<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\BlogPost;
use App\Models\Recipe;
use Illuminate\Support\Str;

final class LlmStructureOptimizerService
{
    /** @return array{score:int,issues:list<array{entity:string,type:string,message:string}>,checked:int} */
    public function scan(): array
    {
        $issues = [];
        $checked = 0;

        Recipe::query()->where('status', 'published')->limit(50)->each(function (Recipe $recipe) use (&$issues, &$checked): void {
            $checked++;
            $this->checkRecipe($recipe, $issues);
        });

        BlogPost::query()->where('editorial_status', 'published')->limit(30)->each(function (BlogPost $post) use (&$issues, &$checked): void {
            $checked++;
            $this->checkBlogPost($post, $issues);
        });

        $score = $checked > 0 ? max(0, 100 - (count($issues) * 5)) : 0;

        return compact('score', 'issues', 'checked');
    }

    private function checkRecipe(Recipe $recipe, array &$issues): void
    {
        $body = $recipe->body ?? '';
        $name = $recipe->title ?? 'Tarif #' . $recipe->id;

        if (Str::length($body) < 200) {
            $issues[] = ['entity' => $name, 'type' => 'thin_content', 'message' => 'İçerik çok kısa (' . Str::length($body) . ' karakter). AI yanıtları için yeterli bilgi yok.'];
        }

        if (! preg_match('/^## /m', $body)) {
            $issues[] = ['entity' => $name, 'type' => 'no_headings', 'message' => 'Bölüm başlıkları yok. AI motorları yapılandırılmış içeriği tercih eder.'];
        }

        if (! Str::contains(Str::lower($body), ['malzemeler', 'adımlar', 'hazırlık', 'pişirme'])) {
            $issues[] = ['entity' => $name, 'type' => 'missing_sections', 'message' => 'Standart bölümler (malzemeler, adımlar) bulunamadı.'];
        }

        if (! Str::contains(Str::lower($body), ['ipucu', 'püf noktası', 'not', 'öneri'])) {
            $issues[] = ['entity' => $name, 'type' => 'no_tips', 'message' => 'Pratik ipuçları eklenmemiş. AI yanıtları faydalı detayları tercih eder.'];
        }
    }

    private function checkBlogPost(BlogPost $post, array &$issues): void
    {
        $body = $post->body ?? '';
        $name = $post->title ?? 'Blog #' . $post->id;

        if (Str::length($body) < 500) {
            $issues[] = ['entity' => $name, 'type' => 'thin_content', 'message' => 'Blog yazısı çok kısa (' . Str::length($body) . ' karakter).'];
        }

        if (! preg_match('/^## /m', $body)) {
            $issues[] = ['entity' => $name, 'type' => 'no_headings', 'message' => 'Bölüm başlıkları yok.'];
        }

        if (! Str::contains(Str::lower($body), ['kaynak', 'referans', 'kaynakça'])) {
            $issues[] = ['entity' => $name, 'type' => 'no_sources', 'message' => 'Kaynak belirtilmemiş. AI motorları kaynaklı içerikleri tercih eder.'];
        }

        if (! preg_match('/\[.+\]\(https?:\/\/.+\)/', $body)) {
            $issues[] = ['entity' => $name, 'type' => 'no_links', 'message' => 'Harici bağlantı yok. Güven sinyali için bağlantılar önemlidir.'];
        }
    }
}
