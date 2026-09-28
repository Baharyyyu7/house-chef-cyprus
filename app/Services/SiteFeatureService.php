<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SiteFeature;
use Illuminate\Support\Facades\Schema;

final class SiteFeatureService
{
    public const DEFINITIONS = [
        'explore' => ['label' => 'Ne Pişirebilirim?', 'description' => 'Malzemeye göre tarif eşleştirme sayfası.'],
        'services' => ['label' => 'Hizmetler', 'description' => 'Özel şef, catering, kurs ve yemek planı sayfaları.'],
        'member_kitchen' => ['label' => 'Mutfağım', 'description' => 'AI Mutfak, dolap, favoriler ve koleksiyonlar.'],
        'premium' => ['label' => 'Premium', 'description' => 'Fiyatlandırma ve yeni abonelik akışı.'],
        'shop' => ['label' => 'Mağaza', 'description' => 'Dijital ürün mağazası ve yeni satın alma akışı.'],
    ];

    /** @var array<string, bool>|null */
    private ?array $states = null;

    public function enabled(string $key): bool
    {
        return $this->states()[$key] ?? false;
    }

    /** @return array<string, bool> */
    public function states(): array
    {
        if ($this->states !== null) {
            return $this->states;
        }

        // Mevcut feature testleri eski açık davranışı sınar. Canlıda yeni
        // gelişmeler güvenli varsayılan olarak kapalı başlar.
        $defaults = array_fill_keys(array_keys(self::DEFINITIONS), app()->environment('testing'));
        if (! Schema::hasTable('site_features')) {
            return $this->states = $defaults;
        }

        return $this->states = array_replace($defaults, SiteFeature::query()->pluck('enabled', 'key')->map(fn ($value): bool => (bool) $value)->all());
    }
}
