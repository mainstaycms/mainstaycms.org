@use('App\Content\Article')
@use('Mainstay\Facades\Mainstay')
@php
    /* Newest first, and of one day the one written last; at depth 1 for
       the tag each is filed under. */
    $articles = Mainstay::paginate(Article::class, sort: ['-publishedOn', '-id'], perPage: $block->perPage, depth: 1);
@endphp
<section class="wrap">
    <x-posts :articles="$articles" />
</section>
