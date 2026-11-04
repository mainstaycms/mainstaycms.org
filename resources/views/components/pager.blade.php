@props(['pages'])
@if ($pages->hasPages())
    <nav class="pager" aria-label="{{ __('Pages') }}">
        @if ($pages->previousPageUrl())
            <a rel="prev" href="{{ $pages->previousPageUrl() }}"><span aria-hidden="true">&larr;</span> {{ __('Newer') }}</a>
        @endif
        <span>{{ __('Page :page of :pages', ['page' => $pages->currentPage(), 'pages' => $pages->lastPage()]) }}</span>
        @if ($pages->nextPageUrl())
            <a rel="next" href="{{ $pages->nextPageUrl() }}">{{ __('Older') }} <span aria-hidden="true">&rarr;</span></a>
        @endif
    </nav>
@endif
