<?php

namespace App\Code;

use Illuminate\Support\HtmlString;
use Mainstay\Content\Block;
use Mainstay\Facades\Mainstay;

/*
 | A block's line of text as HTML, with each span between backticks set as
 | code, as `mainstay:sync` -- a plain Text or Textarea field has no marks to
 | hold it. For the Content tab's editor the field is printed to be typed in
 | place instead, backticks and all, as it is stored.
 */
final class Inline
{
    public static function field(Block $block, string $name): HtmlString
    {
        if (Mainstay::marking()) {
            return Mainstay::field($block, $name);
        }

        return self::html($block->{$name} ?? '');
    }

    public static function html(?string $text): HtmlString
    {
        return new HtmlString(preg_replace('/`([^`]+)`/', '<code>$1</code>', e((string) $text)));
    }
}
