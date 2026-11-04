@use('App\Code\Inline')
@use('App\Content\Phase')
@use('Illuminate\Support\Str')
@use('Mainstay\Facades\Mainstay')
@php
    /* The ones to come in their order; the done ones newest first. */
    $done = $block->showing === 'done';
    $phases = Phase::inOrder();

    /* A phase with no number is named after the one it came after. */
    $after = null;
    $labels = $phases->mapWithKeys(function (Phase $phase) use (&$after) {
        $label = match (true) {
            $phase->number !== null => __('Phase :number', ['number' => $phase->number]),
            $after !== null && $phase->done => __('After :number', ['number' => $after]),
            default => __('Later'),
        };
        $after = $phase->number ?? $after;

        return [$phase->id => $label];
    });

    $phases = $phases->where('done', $done);
    $phases = $done ? $phases->reverse() : $phases;
    $id = 'phases-'.Str::slug($block->heading);
@endphp
<section class="wrap sec" aria-labelledby="{{ $id }}">
    <div class="group">
        <div class="ghead rv">
            <h2 class="h2" id="{{ $id }}">@field($block, 'heading')</h2>
            @if (filled($block->text) || Mainstay::marking())
                <p class="lede">{{ Inline::field($block, 'text') }}</p>
            @endif
        </div>
        <ol @class(['tl', 'done' => $done])>
            @foreach ($phases as $phase)
                <li @class(['item', 'tile', 'rv', 'todo' => ! $phase->done])>
                    <span class="node" aria-hidden="true">
                        @if ($phase->done)
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5 9-10"/></svg>
                        @endif
                    </span>
                    <div class="top">
                        <span class="n">{{ $labels[$phase->id] }}</span>
                        <h3 class="h3">{{ $phase->title }}</h3>
                        @if ($phase->milestone)
                            <span class="badge acc">{{ __('Milestone') }}</span>
                        @endif
                    </div>
                    <p>{{ Inline::html($phase->summary) }}</p>
                </li>
            @endforeach
        </ol>
    </div>
</section>
