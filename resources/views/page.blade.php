{{-- Every page: a way back to the page it is a part of, its head where its
     content does not open on a hero of its own, and its content -- its
     blocks and any text, in the order they were written. --}}
@use('App\Blocks\ArticleList')
@use('App\Blocks\Hero')
@use('App\Code\CodeBlocks')
@use('App\Content\Page')
@use('Illuminate\Pagination\Paginator')
@php
    $opens = ($entry->content?->blocks()[0] ?? null) instanceof Hero;
    $parent = $entry->parent?->missing === false ? $entry->parent : null;
    /* A later page of the blog's list is told apart from its first. */
    $isBlog = isset($entry->id) && $entry->id === Page::holding(ArticleList::class)?->id;
    $listPage = $isBlog ? Paginator::resolveCurrentPage() : 1;
    $title = $listPage > 1 ? __(':title, page :page', ['title' => $entry->title, 'page' => $listPage]) : $entry->title;
@endphp
<x-layout :title="$title" :entry="$entry" :description="$entry->summary">
    @if ($parent)
        <nav class="wrap crumb fadein" aria-label="{{ __('Breadcrumb') }}">
            <a href="{{ $parent->url() }}">{{ $parent->title }}</a><span aria-hidden="true">/</span><span aria-current="page">{{ $entry->title }}</span>
        </nav>
    @endif

    @unless ($opens)
        <header class="wrap page-head">
            <h1 class="h1">{{ $entry->title }}</h1>
            @if ($entry->summary)
                <p class="lede">{{ $entry->summary }}</p>
            @endif
        </header>
    @endunless

    @if ($entry->content)
        <div class="prose page-content">{{ CodeBlocks::highlight($entry->content) }}</div>
    @endif
</x-layout>
