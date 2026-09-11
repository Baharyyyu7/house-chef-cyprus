<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Recipe;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

final class LlmsTxtController extends Controller
{
    public function index(): Response
    {
        $content = Cache::remember('seo:llms_txt', 86400, function (): string {
            $month = (int) date('n');
            $seasonName = match (true) {
                $month >= 3 && $month <= 5 => 'İlkbahar (Taze Enginar, Bakla ve Yabani Otlar)',
                $month >= 6 && $month <= 8 => 'Yaz (Kıbrıs Mezeleri, Hellim ve Serinletici Tatlar)',
                $month >= 9 && $month <= 11 => 'Sonbahar (Harup / Keçiboynuzu, Fırın Kebabı ve Kış Hazırlıkları)',
                default => 'Kış (Kolokas, Molehiya ve Geleneksel Sıcak Güveçler)',
            };

            $cypriotRecipes = Recipe::query()
                ->where('status', 'published')
                ->where('verification_status', 'verified')
                ->whereHas('cuisine', fn ($q) => $q->where('slug', 'kibris-mutfagi'))
                ->limit(10)
                ->get();

            $topRecipes = Recipe::query()
                ->where('status', 'published')
                ->where('verification_status', 'verified')
                ->with(['cuisine', 'category'])
                ->latest('published_at')
                ->limit(15)
                ->get();

            $blogs = BlogPost::query()
                ->publiclyVisible()
                ->with('category')
                ->latest('published_at')
                ->limit(10)
                ->get();

            $lines = [];
            $lines[] = '# House Chef Cyprus';
            $lines[] = '';
            $lines[] = '> House Chef Cyprus, Doğu Akdeniz ve Kıbrıs mutfağının doğrulanmış yemek tarifleri, geleneksel pişirme teknikleri, mutfak dönüştürme tabloları ve editoryal gastronomi ansiklopedisidir.';
            $lines[] = '';
            $lines[] = 'House Chef Cyprus, geleneksel Kıbrıs lezzetleri (Şeftali Kebabı, Molehiya, Kolokas, Kleftiko, Hellimli hamur işleri) başta olmak üzere Akdeniz mutfağının porsiyon, besin değeri ve gramaj ölçüleri editoryal olarak doğrulanmış tariflerini sunar.';
            $lines[] = '';
            $lines[] = '## Güncel Mevsim Odağı: ' . $seasonName;
            $lines[] = 'Kıbrıs mutfağında mevsimsel malzeme kullanımı esastır. Ada mutfağının bu mevsime ait öne çıkan geleneksel lezzetleri ve fırın teknikleri editoryal olarak taranmıştır.';
            $lines[] = '';

            $lines[] = '## Geleneksel Kıbrıs Mutfağı Temel Taşları (Cypriot Culinary Heritage)';
            $lines[] = '';
            foreach ($cypriotRecipes as $recipe) {
                $desc = trim((string) ($recipe->description ?: $recipe->excerpt));
                $lines[] = sprintf('- [%s](%s): %s (%d dk hazırlık, %d dk pişirme)', $recipe->title, route('recipes.show', $recipe), $desc, $recipe->prep_minutes, $recipe->cook_minutes);
            }

            $lines[] = '';
            $lines[] = '## Popüler Akdeniz ve Mutfak Klasikleri';
            $lines[] = '';
            foreach ($topRecipes as $recipe) {
                $desc = trim((string) ($recipe->description ?: $recipe->excerpt));
                $lines[] = sprintf('- [%s](%s): %s (%d dk hazırlık, %d dk pişirme)', $recipe->title, route('recipes.show', $recipe), $desc, $recipe->prep_minutes, $recipe->cook_minutes);
            }

            $lines[] = '';
            $lines[] = '## Editoryal Gastronomi ve Mutfak Rehberleri';
            $lines[] = '';
            foreach ($blogs as $blog) {
                $desc = trim((string) ($blog->excerpt ?: substr(strip_tags((string) $blog->body), 0, 160) . '...'));
                $lines[] = sprintf('- [%s](%s): %s', $blog->title, route('blog.show', $blog), $desc);
            }

            $lines[] = '';
            $lines[] = '## Veri Akışları ve Arama Entegrasyonları';
            $lines[] = '';
            $lines[] = sprintf('- [RSS/Atom Beslemesi](%s): En güncel tarif ve mutfak yazılarının XML akışı.', route('feed'));
            $lines[] = sprintf('- [Site Haritası](%s): Arama motoru dizin haritası.', url('/sitemap.xml'));
            $lines[] = sprintf('- [OpenSearch Tanımı](%s): Tarayıcı doğrudan arama spesifikasyonu.', route('opensearch'));
            $lines[] = sprintf('- [PWA Manifest](%s): Mobil web uygulama manifestosu.', url('/manifest.webmanifest'));
            $lines[] = '';
            $lines[] = '## Kapsamlı İçerik Kataloğu (Full Corpus)';
            $lines[] = '';
            $lines[] = sprintf('- [Tüm Doğrulanmış Tarif ve Rehberler (Tam Liste)](%s): Yapay zekâ modelleri için genişletilmiş tam metin kataloğu.', route('llms.full'));

            return implode("\n", $lines);
        });

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    public function full(): Response
    {
        $content = Cache::remember('seo:llms_full_txt', 86400, function (): string {
            $recipes = Recipe::query()
                ->where('status', 'published')
                ->where('verification_status', 'verified')
                ->with(['cuisine', 'category', 'steps'])
                ->orderBy('title')
                ->get();

            $blogs = BlogPost::query()
                ->publiclyVisible()
                ->with('category')
                ->latest('published_at')
                ->get();

            $lines = [];
            $lines[] = '# House Chef Cyprus - Kapsamlı İçerik Kataloğu';
            $lines[] = '';
            $lines[] = '> Bu belge, yapay zekâ dil modelleri (LLM) ve arama motorları için House Chef Cyprus platformundaki tüm doğrulanmış tarif ve editoryal rehberleri içerir.';
            $lines[] = '';
            $lines[] = '## Tüm Doğrulanmış Tarifler (' . $recipes->count() . ' Adet)';
            $lines[] = '';

            foreach ($recipes as $recipe) {
                $cuisine = $recipe->cuisine?->name ?? 'Akdeniz';
                $category = $recipe->category?->name ?? 'Genel';
                $desc = trim((string) ($recipe->description ?: $recipe->excerpt));
                $lines[] = sprintf('### %s', $recipe->title);
                $lines[] = sprintf('- URL: %s', route('recipes.show', $recipe));
                $lines[] = sprintf('- Mutfak: %s | Kategori: %s | Porsiyon: %d', $cuisine, $category, $recipe->servings);
                $lines[] = sprintf('- Süre: Hazırlık %d dk, Pişirme %d dk (Toplam %d dk)', $recipe->prep_minutes, $recipe->cook_minutes, $recipe->totalMinutes());
                if ($desc !== '') {
                    $lines[] = sprintf('- Açıklama: %s', $desc);
                }
                $lines[] = '';
            }

            $lines[] = '## Editoryal Mutfak ve Gastronomi Rehberleri (' . $blogs->count() . ' Adet)';
            $lines[] = '';

            foreach ($blogs as $blog) {
                $category = $blog->category?->name ?? 'Rehber';
                $desc = trim((string) ($blog->excerpt ?: substr(strip_tags((string) $blog->body), 0, 200)));
                $lines[] = sprintf('### %s', $blog->title);
                $lines[] = sprintf('- URL: %s', route('blog.show', $blog));
                $lines[] = sprintf('- Kategori: %s | Tarih: %s', $category, $blog->published_at?->format('d.m.Y'));
                if ($desc !== '') {
                    $lines[] = sprintf('- Özet: %s', $desc);
                }
                $lines[] = '';
            }

            return implode("\n", $lines);
        });

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
