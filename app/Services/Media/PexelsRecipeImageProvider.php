<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Models\Recipe;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Read-only Pexels candidate search. Results remain review-only: this class
 * never hotlinks, imports, or publishes a remote image.
 */
final class PexelsRecipeImageProvider
{
    /** @return array{name:string,configured:bool,enabled:bool,image_capability:string,model:null,safe_message:string} */
    public function health(): array
    {
        $settings = (array) config('ai_images.providers.pexels');
        $configured = filled($settings['key'] ?? null);
        $enabled = (bool) ($settings['enabled'] ?? false);

        return [
            'name' => 'pexels',
            'configured' => $configured,
            'enabled' => $enabled,
            'image_capability' => ! $configured ? 'not_configured' : (! $enabled ? 'disabled' : 'configured'),
            'model' => null,
            'safe_message' => ! $configured ? 'Pexels aday araması yapılandırılmadı.' : (! $enabled ? 'Pexels aday araması kapalı.' : 'Pexels yalnız private review adayları için hazır.'),
        ];
    }

    /** @return list<array{external_photo_id:string,photographer:string,photographer_url:?string,pexels_page_url:?string,source_url:string,width:int,height:int,alt_text:string,attribution_text:string,score:int}> */
    public function candidates(Recipe $recipe, int $page = 1): array
    {
        return $this->search($recipe, $page)['candidates'];
    }

    /** @return array{status:string,error_code:?string,safe_message:string,candidates:list<array{external_photo_id:string,photographer:string,photographer_url:?string,pexels_page_url:?string,source_url:string,width:int,height:int,alt_text:string,attribution_text:string,score:int}>} */
    public function search(Recipe $recipe, int $page = 1): array
    {
        if (! $this->health()['configured'] || ! $this->health()['enabled']) {
            return $this->result('disabled', 'PEXELS_PROVIDER_DISABLED', 'Pexels aday araması etkin değil.');
        }
        $settings = (array) config('ai_images.providers.pexels');
        $query = $this->queryFor($recipe);
        try {
            $response = Http::withToken((string) $settings['key'])
                ->acceptJson()
                ->timeout(12)
                ->get(rtrim((string) $settings['endpoint'], '/').'/v1/search', [
                    'query' => $query,
                    'orientation' => 'landscape',
                    'size' => 'large',
                    'per_page' => 12,
                    'page' => max(1, min(10, $page)),
                ]);
        } catch (ConnectionException) {
            return $this->result('provider_unavailable', 'PEXELS_PROVIDER_UNAVAILABLE', 'Pexels aday araması güvenli zaman aşımı sınırını geçti.');
        }
        if (! $response->successful()) {
            $code = match ($response->status()) {
                401 => 'PEXELS_INVALID_KEY',
                403 => 'PEXELS_PERMISSION_DENIED',
                429 => 'PEXELS_RATE_LIMITED',
                default => 'PEXELS_PROVIDER_UNAVAILABLE',
            };
            $message = match ($response->status()) {
                401 => 'Pexels anahtarı doğrulanamadı.',
                403 => 'Pexels erişim izni bu istek için yeterli değil.',
                429 => 'Pexels hız sınırı uyguladı; daha sonra yeniden deneyin.',
                default => 'Pexels aday araması şu anda kullanılamıyor.',
            };

            return $this->result('provider_error', $code, $message);
        }
        if (! is_array($response->json('photos'))) {
            return $this->result('provider_error', 'PEXELS_INVALID_RESPONSE', 'Pexels aday yanıtı güvenli biçimde işlenemedi.');
        }

        $candidates = collect($response->json('photos'))->filter(fn (mixed $photo): bool => is_array($photo))
            ->map(function (array $photo) use ($recipe): ?array {
                $source = data_get($photo, 'src.landscape') ?? data_get($photo, 'src.large');
                $id = $photo['id'] ?? null;
                $width = (int) ($photo['width'] ?? 0);
                $height = (int) ($photo['height'] ?? 0);
                if (! is_scalar($id) || ! is_string($source) || ! Str::startsWith($source, 'https://') || $width < 1000 || $height < 600) {
                    return null;
                }
                $photographer = (string) ($photo['photographer'] ?? 'Pexels');
                $alt = trim((string) ($photo['alt'] ?? ''));

                return [
                    'external_photo_id' => (string) $id,
                    'photographer' => $photographer,
                    'photographer_url' => is_string($photo['photographer_url'] ?? null) ? $photo['photographer_url'] : null,
                    'pexels_page_url' => is_string($photo['url'] ?? null) ? $photo['url'] : null,
                    'source_url' => $source,
                    'width' => $width,
                    'height' => $height,
                    'alt_text' => $alt !== '' ? $alt : $recipe->title.' için Pexels aday görseli',
                    'attribution_text' => 'Fotoğraf: '.$photographer.' / Pexels',
                    'score' => $this->score($photo, $recipe),
                ];
            })->filter()->sortByDesc('score')->values()->all();

        return $this->result('ok', null, $candidates === [] ? 'Pexels bu tarif için güvenli aday döndürmedi.' : 'Pexels adayları private review için hazır.', $candidates);
    }

    public function queryFor(Recipe $recipe): string
    {
        $ingredient = $recipe->ingredients()->orderBy('recipe_ingredients.sort_order')->value('ingredients.name');
        $terms = array_filter([$this->englishTitle($recipe->title), $ingredient, $recipe->cuisine?->name, $recipe->category?->name, 'food']);

        return implode(' ', array_unique(array_map(static fn (mixed $term): string => Str::ascii((string) $term), $terms)));
    }

    /** @param array<string,mixed> $photo */
    private function score(array $photo, Recipe $recipe): int
    {
        $alt = Str::lower(Str::ascii((string) ($photo['alt'] ?? '')));
        $tokens = collect(explode(' ', Str::lower(Str::ascii($recipe->title))))->filter(fn (string $token): bool => strlen($token) > 3);
        $matches = $tokens->filter(fn (string $token): bool => str_contains($alt, $token))->count();

        return $matches * 20 + min(30, intdiv((int) ($photo['width'] ?? 0), 100));
    }

    private function englishTitle(string $title): string
    {
        return match (Str::slug($title)) {
            'kibris-usulu-firin-tavuk' => 'Cyprus oven chicken',
            'limonlu-akdeniz-makarnasi' => 'Mediterranean lemon pasta',
            'domates-corbasi' => 'Turkish tomato soup',
            'peynirli-borek' => 'cheese borek pastry',
            'nohutlu-akdeniz-kasesi' => 'chickpea Mediterranean bowl',
            default => Str::ascii($title),
        };
    }

    /** @param list<array{external_photo_id:string,photographer:string,photographer_url:?string,pexels_page_url:?string,source_url:string,width:int,height:int,alt_text:string,attribution_text:string,score:int}> $candidates @return array{status:string,error_code:?string,safe_message:string,candidates:list<array{external_photo_id:string,photographer:string,photographer_url:?string,pexels_page_url:?string,source_url:string,width:int,height:int,alt_text:string,attribution_text:string,score:int}>} */
    private function result(string $status, ?string $errorCode, string $safeMessage, array $candidates = []): array
    {
        return ['status' => $status, 'error_code' => $errorCode, 'safe_message' => $safeMessage, 'candidates' => $candidates];
    }
}
