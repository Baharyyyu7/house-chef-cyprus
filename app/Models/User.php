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
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * @property string|null $public_id
 * @property Carbon|null $last_login_at
 * @property Carbon|null $last_activity_at
 */
#[Fillable(['name', 'first_name', 'last_name', 'email', 'phone', 'country', 'city_or_region', 'preferred_language', 'registration_source', 'terms_accepted_at', 'password', 'role', 'account_status'])]
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
            'terms_accepted_at' => 'datetime',
            'marketing_consent_at' => 'datetime',
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

    /** @return HasMany<RecipeCollection, $this> */
    public function recipeCollections(): HasMany
    {
        return $this->hasMany(RecipeCollection::class);
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

    /** @return HasMany<MemberRegistrationNotification, $this> */
    public function registrationNotifications(): HasMany
    {
        return $this->hasMany(MemberRegistrationNotification::class);
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

    /** @return HasMany<AiShoppingList, $this> */
    public function aiShoppingLists(): HasMany
    {
        return $this->hasMany(AiShoppingList::class);
    }

    /** @return HasOne<PersonalizationProfile, $this> */
    public function personalizationProfile(): HasOne
    {
        return $this->hasOne(PersonalizationProfile::class);
    }

    /** @return HasMany<PersonalizedRecommendation, $this> */
    public function personalizedRecommendations(): HasMany
    {
        return $this->hasMany(PersonalizedRecommendation::class);
    }

    /** @return HasMany<PersonalizationFeedback, $this> */
    public function personalizationFeedback(): HasMany
    {
        return $this->hasMany(PersonalizationFeedback::class);
    }

    /** @return HasMany<PersonalizationExportRequest, $this> */
    public function personalizationExportRequests(): HasMany
    {
        return $this->hasMany(PersonalizationExportRequest::class);
    }

    /** @return HasMany<UserSubscription, $this> */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }

    /** @return HasMany<DigitalOrder, $this> */
    public function digitalOrders(): HasMany
    {
        return $this->hasMany(DigitalOrder::class);
    }

    /** @return HasMany<TipContribution, $this> */
    public function tips(): HasMany
    {
        return $this->hasMany(TipContribution::class);
    }

    public function isPremium(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($this->is_premium) {
            if ($this->premium_expires_at === null || $this->premium_expires_at->isFuture()) {
                return true;
            }
        }

        return false;
    }

    public function canUseAiKitchen(): bool
    {
        if ($this->isPremium()) {
            return true;
        }

        $this->checkAndResetAiDailyUsage();

        // Ücretsiz kullanıcılar için günde 5 AI sorgusu hakkı
        return $this->ai_daily_usage_count < 5;
    }

    public function incrementAiUsage(): void
    {
        if ($this->isPremium()) {
            return;
        }

        $this->checkAndResetAiDailyUsage();
        $this->increment('ai_daily_usage_count');
    }

    public function remainingDailyAiQueries(): int
    {
        if ($this->isPremium()) {
            return 999;
        }

        $this->checkAndResetAiDailyUsage();
        return max(0, 5 - (int) $this->ai_daily_usage_count);
    }

    protected function checkAndResetAiDailyUsage(): void
    {
        $today = Carbon::today();
        if ($this->ai_usage_last_reset_at === null || Carbon::parse($this->ai_usage_last_reset_at)->lt($today)) {
            $this->update([
                'ai_daily_usage_count' => 0,
                'ai_usage_last_reset_at' => $today,
            ]);
        }
    }
}

