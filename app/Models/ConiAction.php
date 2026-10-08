<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Claude Coni'nin canlıya aldığı tek bir değişiklik ve ölçülen sonucu.
 *
 * Sütunlar NOT NULL ve casts() ile Carbon'a çevrilir; phpstan bunları
 * migration'dan string sanıyordu.
 *
 * @property \Illuminate\Support\Carbon $applied_at
 * @property \Illuminate\Support\Carbon $evaluate_after
 */
final class ConiAction extends Model
{
    /** Sayfaya bağlantı ağırlığı veren dönem: iç bağlantı, IndexNow. */
    public const ODAK = 'odak';

    /** Arama sonucunda görünen başlık ve açıklamanın dile özgü değişikliği. */
    public const SERP = 'serp';

    /** Yabancı dildeki kalıp tarif çevirisinin gerçeğiyle değiştirilmesi. */
    public const CEVIRI = 'ceviri';

    public const UYGULANDI = 'uygulandi';

    public const KAZANC = 'kazanc';

    public const KAYIP = 'kayip';

    public const NOTR = 'notr';

    public const GERI_ALINDI = 'geri_alindi';

    public const OLCULEMEDI = 'olculemedi';

    protected $fillable = ['locale', 'page', 'page_hash', 'kind', 'status', 'reason', 'evidence', 'before', 'after', 'baseline', 'result', 'applied_at', 'evaluate_after', 'evaluated_at'];

    protected function casts(): array
    {
        return [
            'evidence' => 'array',
            'before' => 'array',
            'after' => 'array',
            'baseline' => 'array',
            'result' => 'array',
            'applied_at' => 'datetime',
            'evaluate_after' => 'date',
            'evaluated_at' => 'datetime',
        ];
    }

    public static function hash(string $page): string
    {
        return hash('sha256', rtrim($page, '/'));
    }

    /** @param Builder<ConiAction> $query */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status', self::UYGULANDI);
    }
}
