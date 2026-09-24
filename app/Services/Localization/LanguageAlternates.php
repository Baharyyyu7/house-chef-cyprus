<?php

declare(strict_types=1);

namespace App\Services\Localization;

use App\Models\BlogPost;
use App\Models\BlogPostTranslation;
use App\Models\Recipe;
use App\Models\RecipeTranslation;
use Illuminate\Http\Request;

/**
 * Builds reciprocal hreflang maps between the Turkish root edition and the
 * path-prefixed language editions. A URL is only advertised when its
 * counterpart is publicly reachable, so search engines never receive an
 * hreflang pointing at a 404.
 */
final class LanguageAlternates
{
    /** Static page pairs: Turkish route name => [locale => route name, route parameters]. */
    private const STATIC_PAGES = [
        'home' => ['en' => ['en.home', []]],
        'recipes.index' => ['en' => ['en.recipes.index', []]],
        'blog.index' => ['en' => ['en.blog.index', []]],
        'information.show:hakkimizda' => ['en' => ['en.about', []]],
    ];

    /** @return array<string,string> hreflang => absolute URL (x-default included) */
    public function forRequest(Request $request): array
    {
        $route = $request->route();
        if ($route === null) {
            return [];
        }
        $name = (string) $route->getName();
        $routeRecipe = $route->parameter('recipe');
        if ($name === 'recipes.show' && $routeRecipe instanceof Recipe) {
            return $this->forRecipe($routeRecipe);
        }
        $routePost = $route->parameter('post');
        if ($name === 'blog.show' && $routePost instanceof BlogPost) {
            return $this->forPost($routePost);
        }
        $key = $name === 'information.show' ? $name.':'.(string) $route->parameter('page') : $name;

        if (isset(self::STATIC_PAGES[$key])) {
            $urls = ['tr' => $name === 'information.show' ? route($name, (string) $route->parameter('page')) : route($name)];
            foreach (self::STATIC_PAGES[$key] as $locale => [$localizedRoute, $parameters]) {
                $urls[$locale] = route($localizedRoute, $parameters);
            }

            return $this->withDefault($urls);
        }

        $reverse = collect(self::STATIC_PAGES)->first(fn (array $targets): bool => collect($targets)->contains(fn (array $target): bool => $target[0] === $name));
        if ($reverse !== null) {
            $turkishKey = (string) collect(self::STATIC_PAGES)->search($reverse, true);
            [$turkishRoute, $turkishPage] = array_pad(explode(':', $turkishKey, 2), 2, null);
            $urls = ['tr' => $turkishPage !== null ? route($turkishRoute, $turkishPage) : route($turkishRoute)];
            foreach ($reverse as $locale => [$localizedRoute, $parameters]) {
                $urls[$locale] = route($localizedRoute, $parameters);
            }

            return $this->withDefault($urls);
        }

        return [];
    }

    /** @return array<string,string> */
    public function forRecipe(Recipe $recipe): array
    {
        $urls = ['tr' => route('recipes.show', $recipe)];
        RecipeTranslation::query()->where('recipe_id', $recipe->id)->where('status', 'published')
            ->get(['locale', 'slug'])
            ->each(function (RecipeTranslation $translation) use (&$urls): void {
                if ($this->isSupported($translation->locale)) {
                    $urls[$translation->locale] = route($translation->locale.'.recipes.show', $translation->slug);
                }
            });

        return $this->withDefault($urls);
    }

    /** @return array<string,string> */
    public function forRecipeTranslation(RecipeTranslation $translation): array
    {
        $recipe = $translation->recipe;

        return $recipe === null ? [] : $this->forRecipe($recipe);
    }

    /** @return array<string,string> */
    public function forPost(BlogPost $post): array
    {
        $urls = ['tr' => route('blog.show', $post)];
        BlogPostTranslation::query()->where('blog_post_id', $post->id)->where('status', 'published')
            ->get(['locale', 'slug'])
            ->each(function (BlogPostTranslation $translation) use (&$urls): void {
                if ($this->isSupported($translation->locale)) {
                    $urls[$translation->locale] = route($translation->locale.'.blog.show', $translation->slug);
                }
            });

        return $this->withDefault($urls);
    }

    /** @return array<string,string> */
    public function forPostTranslation(BlogPostTranslation $translation): array
    {
        $post = $translation->post;

        return $post === null ? [] : $this->forPost($post);
    }

    /**
     * @param  array<string,string>  $urls  locale => URL
     * @return array<string,string>
     */
    private function withDefault(array $urls): array
    {
        if (count($urls) < 2) {
            return [];
        }
        $hreflangs = [];
        foreach ($urls as $locale => $url) {
            $hreflangs[(string) config("localization.locales.{$locale}.hreflang", $locale)] = $url;
        }
        $default = (string) config('localization.x_default', 'en');
        $hreflangs['x-default'] = $urls[$default] ?? $urls[(string) config('localization.default', 'tr')] ?? reset($urls);

        return $hreflangs;
    }

    private function isSupported(string $locale): bool
    {
        return $locale !== config('localization.default') && array_key_exists($locale, (array) config('localization.locales'));
    }
}
