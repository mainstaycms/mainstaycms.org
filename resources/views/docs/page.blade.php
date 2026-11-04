@use('App\Blocks\DocsList')
@use('App\Code\CodeBlocks')
@use('App\Code\Outline')
@use('App\Content\Doc')
@use('App\Content\Page')
@use('Mainstay\Facades\Mainstay')
@php
    $all = Mainstay::find(Doc::class, sort: 'position', depth: 0);
    $sections = collect(Doc::SECTIONS)->map(fn (string $label, string $section) => $all->where('section', $section))->filter->isNotEmpty();
    $ordered = $sections->flatten(1)->values();
    /* Where this doc is in the order: nowhere yet, when it is not published. */
    $at = $ordered->search(fn (Doc $doc) => $doc->id === ($entry->id ?? null));
    $previous = $at > 0 ? $ordered[$at - 1] : null;
    $next = $at === false ? null : $ordered[$at + 1] ?? null;
    $index = Page::holding(DocsList::class);
    $outline = Outline::of($entry->content);
@endphp
<x-layout :title="$entry->title" :entry="$entry" :description="$entry->summary">
    <div class="wrap shell">
        {{-- Open, and folded away by the page's script on a narrow screen,
             where it would stand before the doc's title. --}}
        <details class="side-menu" open data-side-menu>
            <summary>{{ __('All docs') }}</summary>
            <nav class="side" aria-label="{{ $index?->title ?? __('Documentation') }}">
                @foreach ($sections as $section => $docs)
                    <div>
                        <h2>{{ Doc::SECTIONS[$section] }}</h2>
                        @foreach ($docs as $doc)
                            <a href="{{ $doc->url() }}" @if ($doc->id === ($entry->id ?? null)) aria-current="page" @endif>{{ $doc->title }}</a>
                        @endforeach
                    </div>
                @endforeach
            </nav>
        </details>

        <article class="doc">
            <nav class="crumb" aria-label="{{ __('Breadcrumb') }}">
                @if ($index)
                    <a href="{{ $index->url() }}">{{ $index->title }}</a><span aria-hidden="true">/</span>
                @endif
                <span>{{ Doc::SECTIONS[$entry->section] }}</span>
            </nav>
            <h1 class="h1"><span class="hl"><span>{{ $entry->title }}</span></span></h1>
            @if ($entry->summary)
                <p class="lede fadein">{{ $entry->summary }}</p>
            @endif

            <div class="art prose">{{ CodeBlocks::highlight($outline->html) }}</div>

            <nav class="pager" aria-label="{{ __('Next and previous') }}">
                @if ($previous)
                    <a class="card" rel="prev" href="{{ $previous->url() }}"><span>{{ __('Previous') }}</span><strong><span aria-hidden="true">&larr;</span> {{ $previous->title }}</strong></a>
                @endif
                @if ($next)
                    <a class="card" rel="next" href="{{ $next->url() }}"><span>{{ __('Next') }}</span><strong>{{ $next->title }} <span aria-hidden="true">&rarr;</span></strong></a>
                @endif
            </nav>
        </article>

        @if (count($outline->headings) > 1)
            <nav class="otp" aria-labelledby="on-this-page" data-contents>
                <p id="on-this-page">{{ __('On this page') }}</p>
                @foreach ($outline->headings as $id => $text)
                    <a href="#{{ $id }}">{{ $text }}</a>
                @endforeach
            </nav>
        @endif
    </div>
</x-layout>
