@use('App\Content\Phase')
@use('Mainstay\Facades\Mainstay')
@php
    $phases = Phase::inOrder();
    $done = $phases->where('done', true)->whereNotNull('number');
    $next = $phases->firstWhere('done', false);
@endphp
<section class="wrap progress fadein">
    <x-phase-bar :phases="$phases" />
    <div class="stats">
        <div><p class="stat-label">{{ __('Done') }}</p><p class="stat">{{ $done->isEmpty() ? __('Nothing yet') : __('Phases :first to :last', ['first' => $done->min('number'), 'last' => $done->max('number')]) }}</p></div>
        <div><p class="stat-label">{{ __('Next') }}</p><p class="stat">{{ $next?->title ?? __('Nothing planned') }}</p></div>
        @if (filled($block->testedOn) || Mainstay::marking())
            <div><p class="stat-label">{{ __('Tested on') }}</p><p class="stat">@field($block, 'testedOn')</p></div>
        @endif
    </div>
</section>
