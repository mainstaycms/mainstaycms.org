<?php

namespace Tests\Unit;

use App\Code\Highlighter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/*
 | Each kind of line the site's code blocks hold, coloured, and its text kept
 | to the character, since that text is what the Copy button copies.
 */
class HighlighterTest extends TestCase
{
    public static function samples(): array
    {
        return [
            'PHP' => [
                "#[Route('/blog/{slug}')]\nclass Article extends Entry\n{\n    #[Text(required: true)]\n    public string \$title; // shown first\n}",
                [
                    '<span class="t-attr">#[</span><span class="t-attr">Route</span>(<span class="t-str">&#039;/blog/{slug}&#039;</span>)<span class="t-attr">]</span>',
                    '<span class="t-key">class</span> <span class="t-type">Article</span> <span class="t-key">extends</span> <span class="t-type">Entry</span>',
                    '<span class="t-key">true</span>)<span class="t-attr">]</span>',
                    '<span class="t-key">public</span> <span class="t-type">string</span> <span class="t-var">$title</span>; <span class="t-comment">// shown first</span>',
                ],
            ],
            'Blade' => [
                '@if ($block->page)<a class="button" href="{{ $url }}">@field($block, \'label\')</a>@endif',
                [
                    '<span class="t-key">@if</span> (<span class="t-var">$block</span>-&gt;page)&lt;',
                    '&lt;<span class="t-tag">a</span> <span class="t-attr">class</span>=<span class="t-str">&quot;button&quot;</span>',
                    '<span class="t-attr">href</span>=&quot;<span class="t-key">{{</span> <span class="t-var">$url</span> <span class="t-key">}}</span>&quot;',
                    '<span class="t-key">@field</span>(<span class="t-var">$block</span>, <span class="t-str">&#039;label&#039;</span>)&lt;/<span class="t-tag">a</span>&gt;',
                ],
            ],
            'an attribute holding brackets' => [
                '#[Blocks([FeatureList::class], text: true)]',
                ['<span class="t-key">class</span>], text: <span class="t-key">true</span>)<span class="t-attr">]</span>'],
            ],
            'a tag across lines' => [
                "<a class=\"button\"\n   href=\"{{ \$url }}\">Read the docs</a>",
                ["\n   <span class=\"t-attr\">href</span>=&quot;<span class=\"t-key\">{{</span>"],
            ],
            'PHP, then the Blade printing it' => [
                "#[RichText(localized: true)]\npublic ?Document \$body;\n\n<div class=\"prose\">{{ \$entry->body }}</div>",
                ['<span class="t-key">public</span> ?<span class="t-type">Document</span>', '&lt;<span class="t-tag">div</span>'],
            ],
            'a command' => [
                'php artisan mainstay:user you@example.com --name="Your Name" --role=administrator',
                ['<span class="t-key">php</span> artisan', '<span class="t-attr">--name</span>=<span class="t-str">&quot;Your Name&quot;</span> <span class="t-attr">--role</span>=administrator'],
            ],
            'a request, its URL not read as a PHP comment' => [
                'curl -H "Authorization: Bearer mst_..." https://example.com/api/mainstay/entries/article',
                ['<span class="t-key">curl</span> <span class="t-attr">-H</span> <span class="t-str">&quot;Authorization: Bearer mst_...&quot;</span> https://example.com/api'],
            ],
            'a list of names, left plain' => [
                "edit_articles  publish_articles\nresources/views/components/fields/text.blade.php",
                [],
            ],
        ];
    }

    #[DataProvider('samples')]
    public function test_code_is_coloured_and_keeps_its_text(string $code, array $spans): void
    {
        $html = Highlighter::html($code);

        $this->assertSame($code, htmlspecialchars_decode(strip_tags($html), ENT_QUOTES));

        foreach ($spans as $span) {
            $this->assertStringContainsString($span, $html);
        }

        if ($spans === []) {
            $this->assertSame(e($code), $html);
        }
    }

    public function test_markup_past_pcres_limits_is_drawn_uncoloured(): void
    {
        $code = '@a'.str_repeat('(', 5000).str_repeat(')', 5000);

        $this->assertSame(e($code), Highlighter::html($code));
    }
}
