<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\View\View;

final class LegalInformationCenterController extends Controller
{
    public function index(): View
    {
        /** @var array<int, array{id: string, title: string, paragraphs: array<int, string>}> $sections */
        $sections = config('legal-center.sections');

        return view('legal-information-center.index', [
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
