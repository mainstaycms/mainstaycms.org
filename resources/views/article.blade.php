@use('App\Blocks\ArticleList')
@use('App\Code\CodeBlocks')
@use('App\Code\Outline')
@use('App\Content\Article')
@use('App\Content\Page')
@use('Mainstay\Facades\Mainstay')
@php
    $blog = Page::holding(ArticleList::class);
    /* A tag or an article in the trash, or not written yet, is left out. */
    $tag = collect($entry->tags)->reject->missing->first();
    $outline = Outline::of($entry->content);

    /* Three more to read: the related ones first, then the newest of the rest.
       The related ones are read again a level deeper, for the tag each card
       shows, and kept in the order the article lists them. */
    $relatedIds = collect($entry->related)->reject->missing->pluck('id')->all();
    $related = $relatedIds === [] ? collect() : Mainstay::find(Article::class, where: ['id' => ['in' => $relatedIds]], depth: 1)
        ->sortBy(fn (Article $article) => array_search($article->id, $relatedIds, true))
        ->values();
    $newest = Mainstay::find(Article::class, sort: ['-publishedOn', '-id'], limit: 4, depth: 1)
        ->reject(fn (Article $article) => $article->id === ($entry->id ?? null) || $related->contains('id', $article->id));
    $more = $related->concat($newest)->take(3);
@endphp
<x-layout :title="$entry->title" :entry="$entry" :description="$entry->summary">
    <header class="wrap post-head">
        <nav class="crumb fadein" aria-label="{{ __('Breadcrumb') }}">
            @if ($blog)
                <a href="{{ $blog->url() }}">{{ $blog->title }}</a>
            @endif
            @if ($tag)
                <span aria-hidden="true">/</span><a href="{{ $tag->url() }}">{{ $tag->title }}</a>
            @endif
        </nav>
        <div class="post-title">
            <div class="meta fadein">
                @if ($tag)
                    <span class="badge">{{ $tag->title }}</span>
                @endif
                <x-date :date="$entry->publishedOn" />
            </div>
            <h1 class="h1"><span class="hl"><span>{{ $entry->title }}</span></span></h1>
        </div>
        @if (filled($entry->glyph))
            <x-cover :article="$entry" accent class="post-cover fadein" />
        @endif
    </header>

    <div class="wrap post-body">
        @if (count($outline->headings) > 1)
            <nav class="toc" aria-labelledby="in-this-article" data-contents>
                <p id="in-this-article">{{ __('In this article') }}</p>
                @foreach ($outline->headings as $id => $text)
                    <a href="#{{ $id }}">{{ $text }}</a>
                @endforeach
            </nav>
        @endif
        <div class="post-main">
            <div class="art prose">{{ CodeBlocks::highlight($outline->html) }}</div>
            @if (filled($entry->decision))
                <aside class="endnote rv">
                    <p>{{ __('Every choice behind Mainstay is written down in its decisions log as it is made, with the ones it was weighed against. This article tells one of them.') }}</p>
                    <a class="link" href="{{ $entry->decision }}">{{ __('Read the decision') }} <span class="arr" aria-hidden="true">&rarr;</span></a>
                </aside>
            @endif
        </div>
    </div>

    @if ($more->isNotEmpty())
        <section class="wrap sec more" aria-labelledby="keep-reading">
            <div class="sec-head bare rv">
                <h2 class="h2" id="keep-reading">{{ __('Keep reading') }}</h2>
                @if ($blog)
                    <a class="link" href="{{ $blog->url() }}">{{ __('All articles') }} <span class="arr" aria-hidden="true">&rarr;</span></a>
                @endif
            </div>
            <div class="grid">
                @foreach ($more as $article)
                    <x-post :article="$article" heading="h3" />
                @endforeach
            </div>
        </section>
    @endif
</x-layout>
