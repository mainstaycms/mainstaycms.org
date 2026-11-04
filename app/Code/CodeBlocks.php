<?php

namespace App\Code;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

/*
 | An entry's content with each of its code blocks drawn as the site draws
 | code, in a box, coloured and with a Copy button. Mainstay draws a code
 | block as `<pre><code>` around its escaped text and nothing else, so that
 | is what is looked for: a <pre> drawn any other way, as a code sample's
 | is, holds a tag or an attribute and is left as it is. While the Content
 | tab edits the content it is the editor's JSON, and holds none.
 */
final class CodeBlocks
{
    public static function highlight(?Htmlable $content): HtmlString
    {
        return new HtmlString(preg_replace_callback(
            '#<pre><code>([^<]*)</code></pre>#',
            fn (array $match) => Blade::render('<x-code-box :code="$code" />', ['code' => htmlspecialchars_decode($match[1], ENT_QUOTES)]),
            $content?->toHtml() ?? '',
        ));
    }
}
