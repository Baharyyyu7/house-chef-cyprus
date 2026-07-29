<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Recipe;
use Illuminate\Http\Response;

final class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $staticPaths = ['', 'tarifler', 'hakkimizda', 'nasil-calisir', 'sss', 'editorial-ilkeler', 'tarif-dogrulama-politikasi', 'gorsel-ve-kaynak-politikasi', 'gizlilik', 'kullanim-kosullari', 'cerez-politikasi', 'iletisim'];
        $urls = collect();
        foreach ($staticPaths as $path) {
            $urls->push(['loc' => url($path), 'lastmod' => null]);
        }
        $recipes = Recipe::query()->where('status', 'published')->where('verification_status', 'verified')->orderBy('updated_at')->get(['slug', 'updated_at']);

        foreach ($recipes as $recipe) {
            $urls->push(['loc' => route('recipes.show', $recipe->slug), 'lastmod' => $recipe->updated_at?->toDateString()]);
        }
        BlogPost::query()->publiclyVisible()->orderBy('updated_at')->get(['slug', 'updated_at'])->each(function (BlogPost $post) use ($urls): void {
            $urls->push(['loc' => route('blog.show', $post), 'lastmod' => $post->updated_at?->toDateString()]);
        });

        return response()->view('sitemap', compact('urls'))->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
