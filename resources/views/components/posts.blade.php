@props(['articles', 'current' => null])
@use('App\Blocks\ArticleList')
@use('App\Content\Page')
@use('App\Content\Tag')
@use('Mainstay\Facades\Mainstay')
@php
    /* The blog and each tag, as the filter over the list: the one shown
       marked. */
    $blog = Page::holding(ArticleList::class);
    $tags = Mainstay::find(Tag::class, sort: 'title', depth: 0);
    $featured = $articles->onFirstPage() ? $articles->first() : null;
    $rest = $featured === null ? $articles->items() : array_slice($articles->items(), 1);
@endphp
<div class="posts">
    <nav class="seg filters" aria-label="{{ __('Articles by tag') }}">
        @if ($blog)
            <a href="{{ $blog->url() }}" @if ($current === null) aria-current="page" @endif>{{ __('All') }}</a>
        @endif
        @foreach ($tags as $tag)
            <a href="{{ $tag->url() }}" @if ($current?->id === $tag->id) aria-current="page" @endif>{{ $tag->title }}</a>
        @endforeach
    </nav>

    @if ($featured)
        <x-post :article="$featured" featured />
    @endif

    @if ($rest !== [])
        <div class="grid">
            @foreach ($rest as $article)
                <x-post :article="$article" />
            @endforeach
        </div>
    @endif

    <x-pager :pages="$articles" />
</div>
