<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($entries as $entry)
    <url>
        <loc>{{ $entry->url() }}</loc>
        <lastmod>{{ $entry->updatedAt->format(DATE_ATOM) }}</lastmod>
    </url>
@endforeach
</urlset>
