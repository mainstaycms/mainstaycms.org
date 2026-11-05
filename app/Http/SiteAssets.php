<?php

namespace App\Http;

/*
 | The site's own files every page loads. The stylesheet and the script are
 | stamped with when they changed, so a browser keeps them for good and a
 | deploy that changes one is a new address.
 */
final class SiteAssets
{
    /* Every weight the first screen is drawn in, in the Latin set: text,
       the GitHub link and buttons, and the brand and headings. */
    private const FONTS = [400, 500, 600];

    public static function stylesheet(): string
    {
        return self::stamped('css/site.css');
    }

    public static function script(): string
    {
        return self::stamped('js/site.js');
    }

    /** @return list<string> */
    public static function fonts(): array
    {
        return array_map(fn (int $weight) => "/vendor/mainstay/poppins-{$weight}-latin.woff2", self::FONTS);
    }

    private static function stamped(string $path): string
    {
        return "/{$path}?v=".filemtime(public_path($path));
    }
}
