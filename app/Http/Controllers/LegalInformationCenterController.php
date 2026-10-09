<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\LegalCenterCopy;
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
            ->map(fn (array $bolum): array => LegalCenterCopy::bolum($bolum))
            ->values()
            ->all();

        $ceviri = LegalCenterCopy::ceviri('index') ?? [];
        $title = $ceviri['title'] ?? config('legal-center.title');

        return view('legal-information-center.index', [
            'ayriSayfalar' => collect(config('legal-center.standalone_pages', []))
                ->map(fn (array $tanim, string $anahtar): array => LegalCenterCopy::sayfa($anahtar, $tanim))
                ->all(),
            'sections' => $sections,
            'title' => $title,
            'updatedAt' => $ceviri['updated_at'] ?? config('legal-center.updated_at'),
            'introduction' => $ceviri['introduction'] ?? config('legal-center.introduction'),
            'schema' => [
                '@context' => 'https://schema.org',
                '@type' => 'WebPage',
                'name' => $title,
                'url' => route('legal.center'),
                'inLanguage' => app()->getLocale(),
            ],
        ]);
    }
}
