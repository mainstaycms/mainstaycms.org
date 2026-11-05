<?php

namespace App\Http\Middleware;

use App\Http\SiteAssets;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/*
 | Names the stylesheet and the first screen's fonts in a Link header on
 | every page, so the browser fetches them alongside the HTML instead of
 | after reading it. Cloudflare keeps the header and sends it ahead as Early
 | Hints, while the page itself is still on its way from the server.
 */
class PreloadSiteAssets
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (str_starts_with((string) $response->headers->get('Content-Type'), 'text/html')) {
            $response->headers->set('Link', implode(', ', [
                '<'.SiteAssets::stylesheet().'>; rel=preload; as=style',
                ...array_map(fn (string $font) => "<{$font}>; rel=preload; as=font; type=\"font/woff2\"; crossorigin", SiteAssets::fonts()),
            ]), replace: false);
        }

        return $response;
    }
}
