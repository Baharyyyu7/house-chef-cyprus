<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\LegalCenterCopy;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Kendi adresinde duran politika sayfaları: /gizlilik, /cerezler,
 * /kullanim-kosullari.
 *
 * Bu üçü bir zamanlar yasal merkeze 301 ile toplanmıştı. Kullanıcı tekrar
 * ayrılmalarını istedi. Ayırırken tek kural önemliydi: bölümler kopyalanmaz,
 * taşınır. Aynı metin iki adreste dururken ikisi de yinelenen içerik sayılır
 * ve arama tarafında birbirini zayıflatır; yasal merkez artık bu bölümleri
 * göstermiyor, onlara bağlanıyor.
 */
final class PolicyPageController extends Controller
{
    public function __invoke(string $sayfa): View
    {
        $tanim = config("legal-center.standalone_pages.{$sayfa}");

        abort_if(! is_array($tanim), 404);

        $tanim = LegalCenterCopy::sayfa($sayfa, $tanim);
        $bolumler = $this->bolumler($tanim['bolumler']);

        abort_if($bolumler->isEmpty(), 404);

        return view('legal-information-center.policy', [
            'sayfa' => $sayfa,
            'baslik' => $tanim['baslik'],
            'ozet' => $tanim['ozet'],
            'bolumler' => $bolumler,
            'updatedAt' => LegalCenterCopy::ceviri('index')['updated_at'] ?? config('legal-center.updated_at'),
            'schema' => [
                '@context' => 'https://schema.org',
                '@type' => 'WebPage',
                'name' => $tanim['baslik'],
                'url' => route('policy.'.$sayfa),
                'inLanguage' => app()->getLocale(),
            ],
        ]);
    }

    /**
     * @param  list<string>  $istenen
     * @return Collection<int, array{id: string, title: string, paragraphs: array<int, string>}>
     */
    private function bolumler(array $istenen): Collection
    {
        // Reklam bölümü yalnız reklam açıkken var olur; istenen listede
        // bulunması onun her zaman geleceği anlamına gelmez.
        return collect(config('legal-center.sections'))
            ->whereIn('id', $istenen)
            ->sortBy(fn (array $bolum): int => array_search($bolum['id'], $istenen, true))
            ->values()
            ->map(fn (array $bolum): array => LegalCenterCopy::bolum($bolum));
    }

    /** Yasal merkezde gösterilmeyecek bölüm kimlikleri. */
    public static function tasinanBolumler(): array
    {
        return collect(config('legal-center.standalone_pages', []))
            ->pluck('bolumler')
            ->flatten()
            ->all();
    }
}
