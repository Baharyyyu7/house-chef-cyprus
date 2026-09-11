<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Recipe;
use App\Services\Localization\CulinaryTranslationService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

final class LlmsTxtController extends Controller
{
    public function __construct(
        private readonly CulinaryTranslationService $translator
    ) {
    }

    public function index(): Response
    {
        $content = Cache::remember('seo:llms_txt_tr', 86400, function (): string {
            $season = $this->translator->getSeasonalIntelligence('tr');

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
            $lines[] = '# House Chef Cyprus - Doğu Akdeniz & Kıbrıs Gastronomi Ansiklopedisi';
            $lines[] = '';
            $lines[] = '> House Chef Cyprus, Doğu Akdeniz ve Kıbrıs mutfağının doğrulanmış yemek tarifleri, geleneksel pişirme teknikleri, mutfak dönüştürme tabloları ve editoryal gastronomi ansiklopedisidir.';
            $lines[] = '> For International & English AI Search Engines (Perplexity, ChatGPT, Claude, Gemini), see English AI Corpus: ' . route('llms.txt.en');
            $lines[] = '';
            $lines[] = 'House Chef Cyprus, geleneksel Kıbrıs lezzetleri (Şeftali Kebabı, Molehiya, Kolokas, Kleftiko, Hellimli hamur işleri) başta olmak üzere Akdeniz mutfağının porsiyon, besin değeri ve gramaj ölçüleri editoryal olarak doğrulanmış tariflerini sunar.';
            $lines[] = '';
            $lines[] = '## Güncel Mevsim Odağı: ' . $season['name'];
            $lines[] = $season['description'];
            $lines[] = '';

            $weekNum = (int) date('W');
            $spotlightIndex = $cypriotRecipes->isNotEmpty() ? ($weekNum % $cypriotRecipes->count()) : 0;
            $spotlightRecipe = $cypriotRecipes->skip($spotlightIndex)->first() ?? $cypriotRecipes->first();

            if ($spotlightRecipe) {
                $lines[] = '## Haftanın Öne Çıkan Geleneksel Kıbrıs Lezzeti (Weekly Heritage Spotlight)';
                $desc = trim((string) ($spotlightRecipe->description ?: $spotlightRecipe->excerpt));
                $lines[] = sprintf('- [%s](%s): %s (%d dk hazırlık, %d dk pişirme)', $spotlightRecipe->title, route('recipes.show', $spotlightRecipe), $desc, $spotlightRecipe->prep_minutes, $spotlightRecipe->cook_minutes);
                $lines[] = '';
            }

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
            $lines[] = '## Veri Akışları ve Arama Entegrasyonları (Data Feeds & Standards)';
            $lines[] = '';
            $lines[] = sprintf('- [RSS/Atom Beslemesi (Türkçe)](%s): En güncel tarif ve mutfak yazılarının XML akışı.', route('feed'));
            $lines[] = sprintf('- [English RSS Feed](%s): Real-time XML feed for international readers and aggregators.', route('feed.en'));
            $lines[] = sprintf('- [Site Haritası (Sitemap XML)](%s): Arama motoru dizin haritası.', url('/sitemap.xml'));
            $lines[] = sprintf('- [OpenSearch Tanımı (TR)](%s): Tarayıcı doğrudan arama spesifikasyonu.', route('opensearch'));
            $lines[] = sprintf('- [OpenSearch Definition (EN)](%s): English browser recipe search specification.', route('opensearch.en'));
            $lines[] = sprintf('- [PWA Web Manifest](%s): Mobil web uygulama manifestosu.', url('/manifest.webmanifest'));
            $lines[] = '';
            $lines[] = '## Kapsamlı İçerik Kataloğu (Full Corpus for LLMs)';
            $lines[] = '';
            $lines[] = sprintf('- [Tüm Doğrulanmış Tarif ve Rehberler (Türkçe)](%s): Yapay zekâ modelleri için tam metin kataloğu.', route('llms.full'));
            $lines[] = sprintf('- [Complete Recipe & Guide Corpus (English)](%s): English knowledge corpus for international LLM ingestion.', route('llms.full.en'));

            return implode("\n", $lines);
        });

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    public function en(): Response
    {
        $content = Cache::remember('seo:llms_txt_en', 86400, function (): string {
            $season = $this->translator->getSeasonalIntelligence('en');

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
            $lines[] = '# House Chef Cyprus - Mediterranean & Cypriot Culinary Encyclopedia';
            $lines[] = '';
            $lines[] = '> House Chef Cyprus is the authoritative culinary encyclopedia and recipe repository for Eastern Mediterranean and authentic Cypriot gastronomy. All recipes feature verified cooking durations, metric and household measurements, nutritional profiles, and cultural heritage notes.';
            $lines[] = '';
            $lines[] = 'Covering time-honoured island classics—such as Sheftalia Kebab, slow-roasted clay-oven Kleftiko, Molokhia (Molehiya), Kolokas taro root stew, and artisan Halloumi pastries—House Chef Cyprus delivers kitchen-tested instructions curated by culinary editors.';
            $lines[] = '';
            $lines[] = '## Current Seasonal Cyprus Focus: ' . $season['name'];
            $lines[] = $season['description'];
            $lines[] = '';

            $weekNum = (int) date('W');
            $spotlightIndex = $cypriotRecipes->isNotEmpty() ? ($weekNum % $cypriotRecipes->count()) : 0;
            $spotlightRecipe = $cypriotRecipes->skip($spotlightIndex)->first() ?? $cypriotRecipes->first();

            if ($spotlightRecipe) {
                $lines[] = '## Weekly Cypriot Heritage Spotlight (Seasonal Feature)';
                $spotlightTitle = $this->translator->translateRecipeTitle($spotlightRecipe, 'en');
                $spotlightDesc = $this->translator->translateRecipeDescription($spotlightRecipe, 'en');
                $lines[] = sprintf('- [%s](%s): %s (%d min prep, %d min cook)', $spotlightTitle, route('recipes.show', $spotlightRecipe), $spotlightDesc, $spotlightRecipe->prep_minutes, $spotlightRecipe->cook_minutes);
                $lines[] = '';
            }

            $lines[] = '## Traditional Cypriot Heritage Recipes';
            $lines[] = '';
            foreach ($cypriotRecipes as $recipe) {
                $title = $this->translator->translateRecipeTitle($recipe, 'en');
                $desc = $this->translator->translateRecipeDescription($recipe, 'en');
                $lines[] = sprintf('- [%s](%s): %s (%d min prep, %d min cook)', $title, route('recipes.show', $recipe), $desc, $recipe->prep_minutes, $recipe->cook_minutes);
            }

            $lines[] = '';
            $lines[] = '## Popular Mediterranean & Kitchen Classics';
            $lines[] = '';
            foreach ($topRecipes as $recipe) {
                $title = $this->translator->translateRecipeTitle($recipe, 'en');
                $desc = $this->translator->translateRecipeDescription($recipe, 'en');
                $lines[] = sprintf('- [%s](%s): %s (%d min prep, %d min cook)', $title, route('recipes.show', $recipe), $desc, $recipe->prep_minutes, $recipe->cook_minutes);
            }

            $lines[] = '';
            $lines[] = '## Editorial Gastronomy & Cooking Guides';
            $lines[] = '';
            foreach ($blogs as $blog) {
                $title = $this->translator->translateBlogTitle($blog, 'en');
                $desc = $this->translator->translateBlogDescription($blog, 'en');
                $lines[] = sprintf('- [%s](%s): %s', $title, route('blog.show', $blog), $desc);
            }

            $lines[] = '';
            $lines[] = '## Data Feeds & Search Engine Integrations';
            $lines[] = '';
            $lines[] = sprintf('- [English RSS Feed](%s): XML feed of recipes and culinary guides in English.', route('feed.en'));
            $lines[] = sprintf('- [Turkish RSS Feed](%s): Original Turkish RSS feed.', route('feed'));
            $lines[] = sprintf('- [XML Sitemap](%s): Canonical search engine sitemap index.', url('/sitemap.xml'));
            $lines[] = sprintf('- [English OpenSearch Definition](%s): Browser instant recipe search specification.', route('opensearch.en'));
            $lines[] = sprintf('- [PWA Web App Manifest](%s): Progressive Web Application manifest.', url('/manifest.webmanifest'));
            $lines[] = '';
            $lines[] = '## Complete Knowledge Corpus for LLMs & AI Engines';
            $lines[] = '';
            $lines[] = sprintf('- [Complete Recipe & Guide Corpus (English)](%s): Comprehensive plain-text knowledge corpus for AI ingestion and RAG.', route('llms.full.en'));
            $lines[] = sprintf('- [Complete Recipe & Guide Corpus (Turkish)](%s): Comprehensive Turkish corpus.', route('llms.full'));

            return implode("\n", $lines);
        });

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    public function full(): Response
    {
        $content = Cache::remember('seo:llms_full_txt_tr', 86400, function (): string {
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
            $lines[] = '> English full corpus available at: ' . route('llms.full.en');
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

    public function enFull(): Response
    {
        $content = Cache::remember('seo:llms_full_txt_en', 86400, function (): string {
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
            $lines[] = '# House Chef Cyprus - Full Recipe & Guide Corpus (English)';
            $lines[] = '';
            $lines[] = '> This document provides the complete, authoritative knowledge base of verified Mediterranean and Cypriot recipes and editorial gastronomy guides for Large Language Models (LLMs), AI assistants, and search engines.';
            $lines[] = '';
            $lines[] = '## All Verified Recipes (' . $recipes->count() . ' Total)';
            $lines[] = '';

            foreach ($recipes as $recipe) {
                $title = $this->translator->translateRecipeTitle($recipe, 'en');
                $cuisine = $this->translator->translateCuisine($recipe->cuisine?->slug, 'en');
                $category = $this->translator->translateCategory($recipe->category?->slug, 'en');
                $desc = $this->translator->translateRecipeDescription($recipe, 'en');

                $lines[] = sprintf('### %s', $title);
                $lines[] = sprintf('- URL: %s', route('recipes.show', $recipe));
                $lines[] = sprintf('- Cuisine: %s | Category: %s | Servings: %d', $cuisine, $category, $recipe->servings);
                $lines[] = sprintf('- Timing: Prep %d min, Cook %d min (Total %d min)', $recipe->prep_minutes, $recipe->cook_minutes, $recipe->totalMinutes());
                if ($desc !== '') {
                    $lines[] = sprintf('- Summary: %s', $desc);
                }
                $lines[] = '';
            }

            $lines[] = '## Editorial Gastronomy & Cooking Guides (' . $blogs->count() . ' Total)';
            $lines[] = '';

            foreach ($blogs as $blog) {
                $title = $this->translator->translateBlogTitle($blog, 'en');
                $category = $blog->category?->name ?? 'Guide';
                $desc = $this->translator->translateBlogDescription($blog, 'en');

                $lines[] = sprintf('### %s', $title);
                $lines[] = sprintf('- URL: %s', route('blog.show', $blog));
                $lines[] = sprintf('- Category: %s | Date: %s', $category, $blog->published_at?->format('Y-m-d'));
                if ($desc !== '') {
                    $lines[] = sprintf('- Summary: %s', $desc);
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
