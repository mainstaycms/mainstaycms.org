@props(['title', 'entry' => null, 'description' => null])
@use('App\Blocks\ArticleList')
@use('App\Blocks\DocsList')
@use('App\Content\Article')
@use('App\Content\Doc')
@use('App\Content\Layout')
@use('App\Content\Page')
@use('App\Content\Tag')
@use('App\Http\SiteAssets')
@use('Mainstay\Content\Entry')
@use('Mainstay\Facades\Mainstay')
@php
    $front = Mainstay::frontPage();
    $home = ($front === null ? null : Mainstay::findById(...$front)?->url()) ?? '/';
    $blog = Page::holding(ArticleList::class);
    $index = Page::holding(DocsList::class);
    $layout = Mainstay::global(Layout::class);
    /* What the menu and the footer link to, less what is not there to link. */
    $menu = collect($layout?->menu ?? [])->reject->missing;
    $product = collect($layout?->product ?? [])->reject->missing;
    $reading = collect($layout?->reading ?? [])->reject->missing;
    $repository = rtrim((string) $layout?->repository, '/');

    /* The page shown, or the one it is a part of: the blog for an article or
       a tag, the documentation for a doc, a page's parent for the page. */
    $current = fn (Entry $item) => match (true) {
        $entry !== null && $entry::class === $item::class && ($entry->id ?? null) === $item->id => 'page',
        ($entry instanceof Article || $entry instanceof Tag) && $item->id === $blog?->id,
        $entry instanceof Doc && $item->id === $index?->id,
        $entry instanceof Page && $entry->parent?->id === $item->id => 'true',
        default => null,
    };
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    @if ($description)
        <meta name="description" content="{{ $description }}">
    @endif
    {{-- Only an entry is a page to point search engines and shares at; a 404 is not. --}}
    @if ($entry)
        <link rel="canonical" href="{{ url()->current() }}">
        <meta property="og:title" content="{{ $title }}">
        @if ($description)
            <meta property="og:description" content="{{ $description }}">
        @endif
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:type" content="{{ $entry instanceof Article ? 'article' : 'website' }}">
        <x-structured-data :entry="$entry" />
    @endif
    <link rel="icon" href="/favicon.ico" sizes="32x32">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="{{ SiteAssets::stylesheet() }}">
    <script src="{{ SiteAssets::script() }}" defer></script>
</head>
<body>
    <a class="skip" href="#content">{{ __('Skip to content') }}</a>

    <header class="gn">
        <div class="gn-in">
            <a class="gn-brand" href="{{ $home }}"><x-mark class="gn-mark" />Mainstay</a>

            <nav class="gn-links" aria-label="{{ __('Menu') }}">
                @foreach ($menu as $item)
                    <a href="{{ $item->url() }}" @if ($current($item)) aria-current="{{ $current($item) }}" @endif>{{ $item->title }}</a>
                @endforeach
            </nav>

            @if ($repository !== '')
                <a class="gn-gh" href="{{ $repository }}" aria-label="GitHub">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 .5a11.5 11.5 0 00-3.64 22.41c.58.1.79-.25.79-.56v-2c-3.2.7-3.88-1.37-3.88-1.37-.53-1.33-1.28-1.69-1.28-1.69-1.05-.72.08-.7.08-.7 1.16.08 1.77 1.19 1.77 1.19 1.03 1.77 2.7 1.26 3.36.96.1-.75.4-1.26.73-1.55-2.55-.29-5.24-1.28-5.24-5.69 0-1.26.45-2.29 1.19-3.09-.12-.29-.52-1.46.11-3.05 0 0 .97-.31 3.17 1.18a11 11 0 015.77 0c2.2-1.49 3.17-1.18 3.17-1.18.63 1.59.23 2.76.11 3.05.74.8 1.19 1.83 1.19 3.09 0 4.42-2.7 5.39-5.26 5.68.41.36.78 1.06.78 2.14v3.17c0 .31.21.67.8.56A11.5 11.5 0 0012 .5z"/></svg>
                    <span>GitHub</span>
                </a>
            @endif
        </div>
        <div class="gn-bar" aria-hidden="true"></div>
    </header>

    <main id="content">
        {{ $slot }}
    </main>

    <footer class="gf">
        <div class="gf-in">
            <div class="gf-grid">
                <div>
                    <a class="gn-brand" href="{{ $home }}"><x-mark class="gn-mark" :size="22" />Mainstay</a>
                    @if ($layout?->tagline)
                        <p class="gf-tag">{{ $layout->tagline }}</p>
                    @endif
                </div>
                @if ($product->isNotEmpty())
                    <nav aria-labelledby="footer-product">
                        <h2 id="footer-product">{{ __('Product') }}</h2>
                        @foreach ($product as $item)
                            <a class="l" href="{{ $item->url() }}">{{ $item->title }}</a>
                        @endforeach
                    </nav>
                @endif
                @if ($reading->isNotEmpty())
                    <nav aria-labelledby="footer-reading">
                        <h2 id="footer-reading">{{ __('Docs') }}</h2>
                        @foreach ($reading as $item)
                            <a class="l" href="{{ $item->url() }}">{{ $item->title }}</a>
                        @endforeach
                    </nav>
                @endif
                @if ($repository !== '')
                    <nav aria-labelledby="footer-project">
                        <h2 id="footer-project">{{ __('Project') }}</h2>
                        <a class="l" href="{{ $repository }}">GitHub</a>
                        <a class="l" href="{{ $repository }}/blob/main/docs/plan.md">{{ __('The plan') }}</a>
                        <a class="l" href="{{ $repository }}/blob/main/docs/decisions.md">{{ __('The decisions') }}</a>
                        <a class="l" href="{{ $repository }}/blob/main/LICENSE">{{ __('MIT licence') }}</a>
                    </nav>
                @endif
            </div>
            @if ($layout?->copyright || $layout?->requirements)
                <div class="gf-base"><span>{{ $layout->copyright }}</span><span>{{ $layout->requirements }}</span></div>
            @endif
        </div>
    </footer>
</body>
</html>
