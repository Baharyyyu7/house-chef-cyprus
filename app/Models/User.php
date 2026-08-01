<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * @property string|null $public_id
 * @property Carbon|null $last_login_at
 * @property Carbon|null $last_activity_at
 */
#[Fillable(['name', 'email', 'password', 'role', 'account_status'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    use HasPublicId;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function canReviewFactChecks(): bool
    {
        return in_array($this->role, ['admin', 'fact_check_reviewer'], true);
    }

    public function isContentFactoryStaff(): bool
    {
        return in_array($this->role, ['admin', 'fact_check_reviewer', 'content_editor', 'seo_reviewer', 'media_reviewer'], true) && ! $this->isSystemReviewer();
    }

    public function isSystemReviewer(): bool
    {
        return str_starts_with((string) $this->email, 'system@') || str_starts_with((string) $this->email, 'seed@');
    }

    public function isSuspended(): bool
    {
        return $this->account_status === 'suspended';
    }

    /** @return BelongsToMany<Recipe, $this> */
    public function favoriteRecipes(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class, 'favorites')->withTimestamps();
    }

    /** @return HasMany<PantryItem, $this> */
    public function pantryItems(): HasMany
    {
        return $this->hasMany(PantryItem::class);
    }

    /** @return HasMany<CookingSession, $this> */
    public function cookingSessions(): HasMany
    {
        return $this->hasMany(CookingSession::class);
    }

    /** @return HasMany<UserActivity, $this> */
    public function activities(): HasMany
    {
        return $this->hasMany(UserActivity::class);
    }

    /** @return HasMany<AiConversation, $this> */
    public function aiConversations(): HasMany
    {
        return $this->hasMany(AiConversation::class);
    }

    /** @return HasMany<AiInteraction, $this> */
    public function aiInteractions(): HasMany
    {
        return $this->hasMany(AiInteraction::class);
    }

    /** @return HasMany<AiMealPlan, $this> */
    public function aiMealPlans(): HasMany
    {
        return $this->hasMany(AiMealPlan::class);
    }
}
