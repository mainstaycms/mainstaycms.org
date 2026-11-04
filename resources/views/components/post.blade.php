@props(['article', 'featured' => false, 'heading' => 'h2'])
{{-- An article where it is listed: its cover, its tag and date, its title,
     and its summary where there is room for one. The newest is shown
     largest, its cover beside the rest. --}}
@php
    $tag = collect($article->tags)->reject->missing->first();
@endphp
<a @class(['post', 'rv', 'feat' => $featured]) href="{{ $article->url() }}">
    <x-cover :article="$article" :accent="$featured" />
    <div class="post-text">
        <div class="meta">
            @if ($tag)
                <span class="badge">{{ $tag->title }}</span>
            @endif
            <x-date :date="$article->publishedOn" />
        </div>
        <{{ $heading }} class="title">{{ $article->title }}</{{ $heading }}>
        @if ($article->summary)
            <p class="summary">{{ $article->summary }}</p>
        @endif
        <span class="link">{{ __('Read the article') }} <span class="arr" aria-hidden="true">&rarr;</span></span>
    </div>
</a>
