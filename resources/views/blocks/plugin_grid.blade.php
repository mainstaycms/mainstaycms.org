{{-- The cards, and a search and a filter by what they add, which the
     page's script shows and runs: without it every card is shown. --}}
@php
    $cards = $block->inner();
    $filters = collect($cards)->flatMap->additions()->unique()->values();
@endphp
<section class="wrap plugins" data-plugins>
    <div class="toolbar fadein" hidden>
        <label class="search">
            <x-icon symbol="search" :size="16" />
            <input type="search" placeholder="{{ __('Search plugins') }}" aria-label="{{ __('Search plugins') }}">
        </label>
        <div class="seg" role="group" aria-label="{{ __('Filter by what a plugin adds') }}">
            <button type="button" aria-pressed="true" data-filter="">{{ __('All') }}<small>{{ count($cards) }}</small></button>
            @foreach ($filters as $filter)
                <button type="button" aria-pressed="false" data-filter="{{ $filter }}">{{ $filter }}<small>{{ collect($cards)->filter(fn ($card) => in_array($filter, $card->additions(), true))->count() }}</small></button>
            @endforeach
        </div>
    </div>
    <div class="plugin-grid">@inner($block)</div>
    <p class="empty" hidden>{{ __('No plugin matches that.') }}</p>
</section>
