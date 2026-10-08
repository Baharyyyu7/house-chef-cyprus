<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\RecipeRating;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Üyeliksiz tarif oylama. Kimlik: uzun ömürlü `hc_voter` çerezi (birincil)
 * + IP'nin HMAC karması (ham IP saklanmaz). Aynı çerezin ya da aynı IP
 * karmasının ikinci oyu yeni kayıt değil güncelleme sayılır; toplam oy şişmez.
 */
final class RecipeRatingController extends Controller
{
    public function store(Request $request, Recipe $recipe): JsonResponse
    {
        abort_unless(($recipe->status === 'published' && $recipe->verification_status === 'verified') || $request->user()?->isAdmin(), 404);
        abort_unless($recipe->hasTranslation() || $request->user()?->isAdmin(), 404);

        $validator = validator($request->all(), [
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Geçersiz oy değeri.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $veri = $validator->validated();

        $secmen = $request->cookie('hc_voter');
        if (! is_string($secmen) || strlen($secmen) !== 36) {
            $secmen = (string) Str::uuid();
        }

        $ipKarmasi = hash_hmac('sha256', (string) $request->ip(), (string) config('app.key'));

        // Aynı tarif için aynı ağdan (ip_hash) tek oy: çerezi silip yeniden
        // oylayan yeni seçmen sayılmaz, var olan oyunu günceller. Çerez yine
        // birincil kimlik; IP değiştiren ziyaretçi de kendi oyunu bulur.
        $secmen = DB::transaction(function () use ($recipe, $secmen, $ipKarmasi, $veri): string {
            $mevcut = RecipeRating::query()
                ->where('recipe_id', $recipe->id)
                ->where(fn ($q) => $q->where('voter_key', $secmen)->orWhere('ip_hash', $ipKarmasi))
                ->orderByRaw('CASE WHEN voter_key = ? THEN 0 ELSE 1 END', [$secmen])
                ->lockForUpdate()
                ->first();

            if ($mevcut === null) {
                RecipeRating::query()->create([
                    'recipe_id' => $recipe->id,
                    'voter_key' => $secmen,
                    'rating' => $veri['rating'],
                    'ip_hash' => $ipKarmasi,
                ]);

                return $secmen;
            }

            $mevcut->forceFill(['rating' => $veri['rating'], 'ip_hash' => $ipKarmasi])->save();

            // Çerez kaydın kimliğine eşitlenir; sonraki oy aynı satırı bulur.
            return (string) $mevcut->voter_key;
        });

        $ozet = $recipe->ratingSummary();

        return response()->json([
            'average' => $ozet['average'],
            'count' => $ozet['count'],
            'user_rating' => $veri['rating'],
        ])->cookie('hc_voter', $secmen, 60 * 24 * 365, null, null, true, true, false, 'Lax');
    }
}
