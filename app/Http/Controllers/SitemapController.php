<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Mainstay\Content\Entry;
use Mainstay\Content\GlobalSet;
use Mainstay\Facades\Mainstay;

/*
 | Every published entry with a page of its own, in every locale, with when
 | it last changed: what a search engine is told to crawl. Phases and the
 | layout have no page, so they have no link to list.
 */
class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $entries = collect(Mainstay::registered())
            ->reject(fn (string $type) => is_subclass_of($type, GlobalSet::class))
            ->flatMap(fn (string $type) => collect(array_keys(Mainstay::locales()))->flatMap(fn (string $locale) => Mainstay::find($type, locale: $locale, depth: 0)))
            ->filter(fn (Entry $entry) => $entry->url() !== null);

        return response()->view('seo.sitemap', ['entries' => $entries])->header('Content-Type', 'application/xml');
    }
}
