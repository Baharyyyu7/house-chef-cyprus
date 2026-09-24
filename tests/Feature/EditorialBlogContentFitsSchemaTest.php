<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

/**
 * SQLite varchar uzunluğunu yok sayar, MySQL saymaz: 161 karakterlik bir
 * meta_description yerelde sorunsuz kaydedilip üretimde 1406 "Data too long"
 * ile deploy sonrası içerik uygulamasını durdurdu. Sınırlar statik doğrulanır.
 */
final class EditorialBlogContentFitsSchemaTest extends TestCase
{
    /** Şema ile aynı sınırlar; blog_posts tablosundan alınmıştır. */
    private const LIMITS = [
        'meta_title' => 70,
        'meta_description' => 160,
        'primary_keyword' => 160,
    ];

    public function test_every_editorial_file_fits_the_column_limits(): void
    {
        $offenders = [];

        foreach ($this->editorialFiles() as $slug => $payload) {
            foreach (self::LIMITS as $field => $limit) {
                $value = (string) ($payload[$field] ?? '');
                if (mb_strlen($value) > $limit) {
                    $offenders[] = sprintf('%s · %s: %d/%d karakter', $slug, $field, mb_strlen($value), $limit);
                }
            }
        }

        $this->assertSame([], $offenders, "Kolona sığmayan editoryal alanlar:\n".implode("\n", $offenders));
    }

    public function test_every_editorial_file_carries_the_fields_the_pipeline_expects(): void
    {
        $incomplete = [];

        foreach ($this->editorialFiles() as $slug => $payload) {
            foreach (['title', 'excerpt', 'body', 'meta_title', 'meta_description', 'primary_keyword'] as $field) {
                if (blank($payload[$field] ?? null)) {
                    $incomplete[] = $slug.' · eksik: '.$field;
                }
            }

            foreach ($payload['faq'] ?? [] as $index => $faq) {
                if (blank($faq['question'] ?? null) || blank($faq['answer'] ?? null)) {
                    $incomplete[] = $slug.' · SSS #'.$index.' eksik soru veya cevap';
                }
            }
        }

        $this->assertSame([], $incomplete, implode("\n", $incomplete));
    }

    /** @return array<string, array<string, mixed>> */
    private function editorialFiles(): array
    {
        $files = glob(resource_path('editorial/blog-content/*.php')) ?: [];
        $payloads = [];

        foreach ($files as $file) {
            $payloads[basename($file, '.php')] = require $file;
        }

        return $payloads;
    }
}
