<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasPublicId;

    /**
     * Tohumlamadan kalan genel açıklama metni.
     *
     * Sekiz kategorinin hepsi birebir bu metni taşıyor, gerisi boş: yani
     * hiçbir kategorinin gerçek açıklaması yok. Sayfada göstermek, içerik
     * varmış izlenimi veren dolgu metin basmak olurdu.
     *
     * Sabit burada duruyor çünkü şablona gömülü bir metin, metin değişince
     * sessizce çalışmayı bırakır ve dolgu yeniden görünür.
     */
    public const YER_TUTUCU_ACIKLAMA = 'House Chef Cyprus editoryal içerik kategorisi.';

    protected $fillable = ['name', 'slug', 'description'];

    /** Gösterilmeye değer bir açıklaması var mı? */
    public function gercekAciklamasiVar(): bool
    {
        $aciklama = trim((string) $this->description);

        return $aciklama !== '' && $aciklama !== self::YER_TUTUCU_ACIKLAMA;
    }

    /** @return HasMany<Recipe, $this> */
    public function recipes(): HasMany
    {
        return $this->hasMany(Recipe::class);
    }
}
