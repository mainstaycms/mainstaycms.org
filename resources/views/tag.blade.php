@use('App\Blocks\ArticleList')
@use('App\Content\Article')
@use('App\Content\Page')
@use('Mainstay\Facades\Mainstay')
@php
    /* None for a tag not yet written, which the Content tab draws. */
    $articles = isset($entry->id) ? Mainstay::paginate(Article::class, where: ['tags' => $entry->id], sort: ['-publishedOn', '-id'], perPage: 10, depth: 1) : null;
    $blog = Page::holding(ArticleList::class);
    $title = $articles?->currentPage() > 1 ? __(':title, page :page', ['title' => $entry->title, 'page' => $articles->currentPage()]) : $entry->title;
@endphp
<x-layout :title="$title" :entry="$entry" :description="__('Articles about :tag', ['tag' => $entry->title])">
    <header class="wrap page-head">
        @if ($blog)
            <p class="eyebrow fadein"><a href="{{ $blog->url() }}">{{ $blog->title }}</a></p>
        @endif
        <h1 class="h1"><span class="hl"><span>{{ $entry->title }}</span></span></h1>
    </header>

    @if ($articles)
        <section class="wrap">
            <x-posts :articles="$articles" :current="$entry" />
        </section>
    @endif
</x-layout>
