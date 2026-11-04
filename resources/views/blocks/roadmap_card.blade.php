@use('App\Code\Inline')
@use('App\Content\Phase')
@use('Mainstay\Facades\Mainstay')
@php
    $phases = Phase::inOrder();
    $done = $phases->where('done', true)->whereNotNull('number');
    $next = $phases->firstWhere('done', false);
    $href = $block->page === null || $block->page->missing ? null : $block->page->url();
@endphp
<a class="card roadmap-card rv" @if ($href) href="{{ $href }}" @endif>
    <div>
        <h3 class="h3">
            @if ($done->isEmpty())
                {{ __('No phase is done yet') }}
            @else
                {{ __('Phases :first to :last are done', ['first' => $done->min('number'), 'last' => $done->max('number')]) }}
            @endif
        </h3>
        <p>{{ Inline::field($block, 'text') }}</p>
    </div>
    <div>
        <x-phase-bar :phases="$phases" />
        <div class="roadmap-next">
            @if ($next)
                <span>{{ __('Next') }}: <strong>{{ $next->title }}</strong></span>
            @endif
            <span class="link">{{ __('See the roadmap') }} <span class="arr" aria-hidden="true">&rarr;</span></span>
        </div>
    </div>
</a>
