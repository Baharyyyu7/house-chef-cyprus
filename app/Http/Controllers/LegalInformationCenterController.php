<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\View\View;

final class LegalInformationCenterController extends Controller
{
    public function index(): View
    {
        // Gizlilik, çerez ve kullanım koşulları kendi sayfalarına taşındı.
        // Burada tekrar gösterilmezler: aynı metin iki adreste dururken ikisi
        // de yinelenen içerik sayılır ve birbirini zayıflatır. Merkez kalan
        // bölümleri tutar, taşınanlara bağlanır.
        $tasinan = PolicyPageController::tasinanBolumler();

        /** @var array<int, array{id: string, title: string, paragraphs: array<int, string>}> $sections */
        $sections = collect(config('legal-center.sections'))
            ->reject(fn (array $bolum): bool => in_array($bolum['id'], $tasinan, true))
            ->values()
            ->all();

        return view('legal-information-center.index', [
            'ayriSayfalar' => config('legal-center.standalone_pages', []),
            'sections' => $sections,
            'title' => config('legal-center.title'),
            'updatedAt' => config('legal-center.updated_at'),
            'introduction' => config('legal-center.introduction'),
            'schema' => [
                '@context' => 'https://schema.org',
                '@type' => 'WebPage',
                'name' => config('legal-center.title'),
                'url' => route('legal.center'),
                'inLanguage' => 'tr',
            ],
        ]);
    }
}
