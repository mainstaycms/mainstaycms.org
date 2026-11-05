{{-- What a search engine reads about the page beyond its text: the product on
     the front page, and each article as a post with the day it was written. --}}
@props(['entry' => null])
@use('App\Content\Article')
@use('App\Content\Page')
@php
    $data = match (true) {
        $entry instanceof Page && $entry->isFront() => [
            '@context' => 'https://schema.org',
            '@type' => 'SoftwareApplication',
            'name' => 'Mainstay',
            'description' => $entry->summary,
            'url' => url()->current(),
            'applicationCategory' => 'DeveloperApplication',
            'license' => 'https://opensource.org/licenses/MIT',
            'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'EUR'],
        ],
        $entry instanceof Article => [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $entry->title,
            'description' => $entry->summary,
            'url' => $entry->url(),
            'datePublished' => $entry->publishedOn->toDateString(),
            'dateModified' => $entry->updatedAt->format(DATE_ATOM),
        ],
        default => null,
    };
@endphp
@if ($data !== null)
    <script type="application/ld+json">@json($data, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG)</script>
@endif
