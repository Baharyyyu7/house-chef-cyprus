<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\CookingSession;
use App\Models\CookingTimer;
use App\Models\Ingredient;
use App\Models\PantryItem;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class SeedQaFixtures extends Command
{
    protected $signature = 'qa:seed-fixtures {--reset : Sadece local/testing fixture kayıtlarını sıfırla} {--json : Makine okunabilir çıktı} {--validate-only : Kayıt oluşturmadan fixture durumunu doğrula}';

    protected $description = 'Yerel tarayıcı QA için tekrarlanabilir admin ve member fixture kayıtları oluşturur';

    public function handle(): int
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->error('QA fixture komutu yalnız local veya testing ortamında çalışır.');

            return self::FAILURE;
        }

        if ($this->option('reset')) {
            $this->resetFixtures();
        }

        if (! $this->option('validate-only')) {
            $this->seedFixtures();
        }

        $result = $this->result();
        if ($this->option('json')) {
            $this->line((string) json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        } else {
            $this->info('QA fixture durumu: admin '.$result['admin'].' · member A '.$result['member_a'].' · member B '.$result['member_b']);
            $this->line('Favori '.$result['favorites'].' · pantry '.$result['pantry_items'].' · devam eden oturum '.$result['active_sessions']);
        }

        return self::SUCCESS;
    }

    private function resetFixtures(): void
    {
        DB::transaction(function (): void {
            User::query()->whereIn('email', ['member-a@housechef.local', 'member-b@housechef.local'])->each(function (User $user): void {
                $user->delete();
            });
        });
    }

    private function seedFixtures(): void
    {
        $recipe = Recipe::query()->where('status', 'published')->orderBy('id')->firstOrFail();
        $ingredient = Ingredient::query()->orderBy('id')->firstOrFail();
        $step = $recipe->steps()->orderBy('position')->first();

        DB::transaction(function () use ($recipe, $ingredient, $step): void {
            $admin = $this->ensureUser('admin@housechef.local', 'House Chef Yerel Yönetici', 'admin');
            $memberA = $this->ensureUser('member-a@housechef.local', 'QA Member A', 'member');
            $memberB = $this->ensureUser('member-b@housechef.local', 'QA Member B', 'member');

            $memberA->favoriteRecipes()->syncWithoutDetaching([$recipe->id]);
            PantryItem::query()->updateOrCreate(['user_id' => $memberA->id, 'ingredient_id' => $ingredient->id], ['quantity' => '2.0000', 'canonical_quantity' => '200.0000', 'canonical_unit' => 'g', 'unit' => 'g', 'conversion_confidence' => 'verified', 'is_available' => true, 'storage_location' => 'pantry']);
            PantryItem::query()->updateOrCreate(['user_id' => $memberB->id, 'ingredient_id' => $ingredient->id], ['quantity' => '1.0000', 'canonical_quantity' => '100.0000', 'canonical_unit' => 'g', 'unit' => 'g', 'conversion_confidence' => 'verified', 'is_available' => true, 'storage_location' => 'fridge']);

            $session = CookingSession::query()->updateOrCreate(['user_id' => $memberA->id, 'recipe_id' => $recipe->id, 'status' => 'in_progress'], ['current_step_id' => $step?->id, 'servings_snapshot' => max(1, (int) $recipe->servings), 'started_at' => now()->subMinutes(10), 'last_activity_at' => now()]);
            if ($step !== null) {
                CookingTimer::query()->updateOrCreate(['cooking_session_id' => $session->id, 'recipe_step_id' => $step->id, 'label' => 'QA zamanlayıcısı'], ['duration_seconds' => 300, 'remaining_seconds' => 240, 'status' => 'paused', 'paused_at' => now()]);
            }
            // Theme is deliberately client-side localStorage; it is not persisted on a user record.
            unset($admin);
        });
    }

    private function ensureUser(string $email, string $name, string $role): User
    {
        $user = User::query()->firstOrCreate(['email' => $email], ['name' => $name, 'password' => 'ChangeMe123!', 'email_verified_at' => now(), 'role' => $role]);
        $user->forceFill(['name' => $name, 'role' => $role, 'email_verified_at' => $user->email_verified_at ?? now()])->save();

        return $user;
    }

    /** @return array<string, int|bool> */
    private function result(): array
    {
        $memberA = User::query()->where('email', 'member-a@housechef.local')->first();

        return [
            'environment_allowed' => true,
            'admin' => User::query()->where('email', 'admin@housechef.local')->count(),
            'member_a' => $memberA === null ? 0 : 1,
            'member_b' => User::query()->where('email', 'member-b@housechef.local')->count(),
            'favorites' => $memberA?->favoriteRecipes()->count() ?? 0,
            'pantry_items' => $memberA?->pantryItems()->count() ?? 0,
            'active_sessions' => $memberA?->cookingSessions()->where('status', 'in_progress')->count() ?? 0,
        ];
    }
}
