<?php

namespace App\Code;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/*
 | An entry's content with each second-level heading given an id from its
 | text, and those headings by id: the list beside an article or a doc that
 | leads to its parts. While the Content tab edits the content it is the
 | editor's JSON, which holds no heading to find.
 */
final class Outline
{
    /** @param array<string, string> $headings each heading's text by its id */
    private function __construct(public readonly HtmlString $html, public readonly array $headings) {}

    public static function of(?Htmlable $content): self
    {
        $headings = [];

        $html = preg_replace_callback('#<h2>(.*?)</h2>#s', function (array $match) use (&$headings) {
            $text = html_entity_decode(strip_tags($match[1]), ENT_QUOTES);
            $id = $base = Str::slug($text) ?: 'part';

            for ($count = 2; isset($headings[$id]); $count++) {
                $id = "{$base}-{$count}";
            }

            $headings[$id] = $text;

            return '<h2 id="'.$id.'">'.$match[1].'</h2>';
        }, $content?->toHtml() ?? '');

        return new self(new HtmlString($html), $headings);
    }
}
