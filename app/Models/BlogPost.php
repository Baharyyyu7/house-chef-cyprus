<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property Carbon|null $published_at
 * @property Carbon|null $scheduled_at
 * @property Carbon|null $updated_at
 */
final class BlogPost extends Model
{
    use HasPublicId;
    use HasTranslations {
        hasTranslation as private alanlariCevrildi;
        scopeTranslatedInto as private alanlarIcinSuz;
    }

    /**
     * Yazı bu dilde gerçekten çevrilmiş mi.
     *
     * Alanların dolu olması yetmiyor: 28 Eylül 2026'da İngilizce gövdelerin
     * 150'si aynı 30 cümleydi, Almanca başlıklar Türkçe bırakılıp arkasına
     * "Umfassender Ratgeber" eklenmişti. Kalıp çeviri o dilde yok sayılır;
     * liste, site haritası ve hreflang aynı tanımı paylaşır.
     */
    public function hasTranslation(?string $locale = null): bool
    {
        $locale ??= app()->getLocale();

        return $this->alanlariCevrildi($locale)
            && ! app(\App\Services\Seo\BlogTranslationGate::class)->kalipMi($this, $locale);
    }

    public function scopeTranslatedInto(Builder $query, ?string $locale = null): Builder
    {
        $locale ??= app()->getLocale();
        $query = $this->alanlarIcinSuz($query, $locale);
        $kalip = app(\App\Services\Seo\BlogTranslationGate::class)->kalipYazilar($locale);

        return $kalip === [] ? $query : $query->whereNotIn($this->qualifyColumn('id'), $kalip);
    }
    use \App\Models\Concerns\RedirectsOldSlugs;
    use \App\Models\Concerns\GuardsPublication;

    protected function yayinIcinInsanOnayiGerekir(): bool
    {
        return true;
    }

    public function showRouteName(): string
    {
        return 'blog.show';
    }


    /**
     * Bir yazı sayfasını taşıyan alanlar.
     *
     * @return list<string>
     */
    public function translatableFields(): array
    {
        return ['title', 'excerpt', 'body'];
    }

    protected $fillable = ['author_id', 'human_approved_by_user_id', 'blog_category_id', 'hero_media_id', 'content_brief_id', 'content_factory_batch_id', 'title', 'slug', 'excerpt', 'body', 'content_purpose', 'search_intent', 'primary_keyword', 'secondary_keywords', 'source_notes', 'faq_blocks', 'status', 'fact_check_status', 'editorial_status', 'content_factory_state', 'content_factory_readiness_score', 'content_factory_rule_version', 'content_factory_checked_at', 'content_factory_metadata', 'fact_check_required', 'originality_status', 'freshness_class', 'scheduled_at', 'review_due_at', 'human_approved_at', 'published_at', 'last_refreshed_at', 'meta_title', 'meta_description', 'canonical_url'];

    protected function casts(): array
    {
        return ['scheduled_at' => 'datetime', 'review_due_at' => 'datetime', 'human_approved_at' => 'datetime', 'published_at' => 'datetime', 'last_refreshed_at' => 'datetime', 'content_factory_checked_at' => 'datetime', 'fact_check_required' => 'boolean', 'secondary_keywords' => 'array', 'source_notes' => 'array', 'faq_blocks' => 'array', 'content_factory_metadata' => 'array'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Gövde düz metin olarak nl2br ile basılıyordu: uzun bir yazı tek blok duvar
     * metne dönüşüyor, ara başlık ve liste kurulamıyordu. Markdown alt kümesiyle
     * yapı kurulur; ham HTML girişi çıkarılır ve güvensiz bağlantılar engellenir,
     * böylece içerik editörden gelse bile işaretleme enjekte edilemez.
     */
    public function renderedBody(): string
    {
        $html = Str::markdown((string) $this->translated('body'), [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        // Gövdede H1 varsa bütün başlıklar bir kademe aşağı iner: # → h2,
        // ## → h3.
        //
        // Sayfanın H1'i yazı başlığı ve şablonda basılıyor. 14 Eylül 2026'da
        // yayındaki 248 yazının 177'sinin gövdesi "# Başlık" satırıyla
        // başlıyordu; her sayfada ikinci bir H1 çıkıyordu.
        //
        // Kaydırma koşullu: gövdesi zaten "##" ile başlayan yazılar doğru
        // yazılmıştır. Onları da kaydırmak ara başlıklarını H3'e indirir ve
        // sayfa H1'den H3'e atlardı - bir kusuru düzeltirken başka bir
        // hiyerarşi kusuru yaratmak.
        if (preg_match('/<h1[\s>]/i', $html) !== 1) {
            return $html;
        }

        return preg_replace_callback(
            '/<(\/?)h([1-6])(?=[\s>])/i',
            static fn (array $e): string => '<'.$e[1].'h'.min(6, (int) $e[2] + 1),
            $html
        ) ?? $html;
    }

    /**
     * Okurun gördüğü gövde, düz metin.
     *
     * Uzunluk eşikleri ham gövdeden ölçülüyordu. 8 Ekim 2026'da 21 yazının
     * gövdesi ham HTML'di: renderedBody() onu siliyor, sayfa boş çıkıyor,
     * ama strip_tags ham metni saydığı için yazılar "uzun" görünüp
     * indeksteydi. Ölçü sayfada basılanla aynı olmalı.
     */
    public function okunanMetin(): string
    {
        $metin = html_entity_decode(strip_tags($this->renderedBody()), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim((string) preg_replace('/\s+/u', ' ', $metin));
    }

    /** @param Builder<self> $query @return Builder<self> */
    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->where('status', 'published')->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /** @return BelongsTo<BlogCategory, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    /** @return BelongsTo<ContentFactoryBatch, $this> */
    public function contentFactoryBatch(): BelongsTo
    {
        return $this->belongsTo(ContentFactoryBatch::class);
    }

    /** @return BelongsTo<Media, $this> */
    public function heroMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'hero_media_id');
    }

    /** @return MorphMany<Media, $this> */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    /** @return BelongsToMany<BlogTag, $this> */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(BlogTag::class);
    }

    /** @return BelongsToMany<Recipe, $this> */
    public function recipes(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class);
    }

    /** @return BelongsTo<ContentBrief, $this> */
    public function brief(): BelongsTo
    {
        return $this->belongsTo(ContentBrief::class, 'content_brief_id');
    }

    /** @return MorphOne<SeoMetadata, $this> */
    public function seoMetadata(): MorphOne
    {
        return $this->morphOne(SeoMetadata::class, 'seoable');
    }

    /** @return HasMany<ContentRevision, $this> */
    public function revisions(): HasMany
    {
        return $this->hasMany(ContentRevision::class);
    }

    /** @return HasMany<SocialDistributionDraft, $this> */
    public function socialDrafts(): HasMany
    {
        return $this->hasMany(SocialDistributionDraft::class);
    }
}
