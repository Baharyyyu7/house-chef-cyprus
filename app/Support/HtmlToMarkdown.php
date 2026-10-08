<?php

declare(strict_types=1);

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;

/**
 * Yazı gövdesindeki ham HTML'i Markdown'a çevirir.
 *
 * Gövde Markdown olarak basılıyor ve ham HTML siliniyor (BlogPost::renderedBody).
 * 8 Ekim 2026'da 21 yayındaki yazının gövdesi HTML'di ve sayfada boş çıkıyordu.
 * Yalnız gövdelerde görülen etiketler desteklenir; bilinmeyen etiketin metni
 * korunur, etiketi düşer.
 */
final class HtmlToMarkdown
{
    public function convert(string $html): string
    {
        $dom = new DOMDocument;
        $onceki = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8"><body>'.$html.'</body>', LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        libxml_use_internal_errors($onceki);

        $body = $dom->getElementsByTagName('body')->item(0);
        $markdown = $body === null ? '' : $this->blocks($body);

        return trim((string) preg_replace("/\n{3,}/", "\n\n", $markdown))."\n";
    }

    private function blocks(DOMNode $node): string
    {
        $out = '';
        $inline = '';

        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMElement && $this->isBlock($child)) {
                if (trim($inline) !== '') {
                    $out .= trim($inline)."\n\n";
                }
                $inline = '';
                $out .= $this->block($child);
            } else {
                $inline .= $this->inline($child);
            }
        }

        if (trim($inline) !== '') {
            $out .= trim($inline)."\n\n";
        }

        return $out;
    }

    private function isBlock(DOMElement $el): bool
    {
        return in_array(strtolower($el->tagName), ['p', 'div', 'section', 'article', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'ol', 'table', 'blockquote', 'hr', 'pre', 'figure'], true);
    }

    private function block(DOMElement $el): string
    {
        $tag = strtolower($el->tagName);

        return match (true) {
            preg_match('/^h([1-6])$/', $tag, $m) === 1 => str_repeat('#', (int) $m[1]).' '.$this->text($el)."\n\n",
            $tag === 'p' => $this->text($el)."\n\n",
            $tag === 'ul', $tag === 'ol' => $this->list($el, 0)."\n",
            $tag === 'table' => $this->table($el)."\n",
            $tag === 'blockquote' => implode("\n", array_map(static fn (string $l): string => rtrim('> '.$l), explode("\n", trim($this->blocks($el)))))."\n\n",
            $tag === 'hr' => "---\n\n",
            $tag === 'pre' => "```\n".trim($el->textContent)."\n```\n\n",
            default => $this->blocks($el),
        };
    }

    private function list(DOMElement $list, int $depth): string
    {
        $out = '';
        $sira = 1;
        $sirali = strtolower($list->tagName) === 'ol';

        foreach ($list->childNodes as $li) {
            if (! $li instanceof DOMElement || strtolower($li->tagName) !== 'li') {
                continue;
            }

            $metin = '';
            $alt = '';
            foreach ($li->childNodes as $child) {
                if ($child instanceof DOMElement && in_array(strtolower($child->tagName), ['ul', 'ol'], true)) {
                    $alt .= $this->list($child, $depth + 1);
                } elseif ($child instanceof DOMElement && strtolower($child->tagName) === 'p') {
                    $metin .= ' '.$this->text($child);
                } else {
                    $metin .= $this->inline($child);
                }
            }

            $isaret = $sirali ? ($sira++).'.' : '-';
            $out .= str_repeat('  ', $depth).$isaret.' '.$this->clean($metin)."\n".$alt;
        }

        return $out;
    }

    private function table(DOMElement $table): string
    {
        $satirlar = [];
        foreach ($table->getElementsByTagName('tr') as $tr) {
            $hucreler = [];
            foreach ($tr->childNodes as $td) {
                if ($td instanceof DOMElement && in_array(strtolower($td->tagName), ['td', 'th'], true)) {
                    $hucreler[] = str_replace('|', '\|', $this->text($td));
                }
            }
            if ($hucreler !== []) {
                $satirlar[] = $hucreler;
            }
        }

        if ($satirlar === []) {
            return '';
        }

        $genislik = max(array_map('count', $satirlar));
        $satirlar = array_map(static fn (array $s): array => array_pad($s, $genislik, ''), $satirlar);
        $out = '| '.implode(' | ', $satirlar[0])." |\n|".str_repeat(' --- |', $genislik)."\n";
        foreach (array_slice($satirlar, 1) as $satir) {
            $out .= '| '.implode(' | ', $satir)." |\n";
        }

        return $out;
    }

    private function text(DOMNode $node): string
    {
        $out = '';
        foreach ($node->childNodes as $child) {
            $out .= $this->inline($child);
        }

        return $this->clean($out);
    }

    private function inline(DOMNode $node): string
    {
        if ($node instanceof DOMText) {
            return (string) preg_replace('/\s+/u', ' ', $node->nodeValue ?? '');
        }

        if (! $node instanceof DOMElement) {
            return '';
        }

        $ic = $this->text($node);

        return match (strtolower($node->tagName)) {
            'strong', 'b' => $ic === '' ? '' : '**'.$ic.'**',
            'em', 'i' => $ic === '' ? '' : '*'.$ic.'*',
            'code' => '`'.$ic.'`',
            'br' => "  \n",
            'a' => $this->link($node, $ic),
            'img', 'script', 'style' => '',
            default => $ic,
        };
    }

    private function link(DOMElement $a, string $metin): string
    {
        $href = trim($a->getAttribute('href'));
        if ($href === '' || preg_match('#^(https?:|/|\#|mailto:)#i', $href) !== 1) {
            return $metin;
        }

        return '['.($metin === '' ? $href : $metin).']('.$href.')';
    }

    private function clean(string $metin): string
    {
        return trim((string) preg_replace('/[ \t]+/u', ' ', str_replace("\u{00A0}", ' ', $metin)));
    }
}
