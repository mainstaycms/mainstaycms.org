@use('Mainstay\Facades\Mainstay')
@if ($block->image?->missing === false || Mainstay::marking())
    <figure @class(['image', 'rv' => $block->style !== 'tilt'])>
        <div @class(['frame' => $block->style !== 'plain', 'crop' => $block->style === 'plain', 'tilt' => $block->style === 'tilt'])>
            {{-- A tilted image opens a page under its hero, so it is what the first screen waits for. Eager, it is
                 not sized by its layout, so it is told the page's width inside the gutters. --}}
            {{ $block->image?->picture('wide', $block->style === 'tilt' ? [
                'loading' => 'eager',
                'fetchpriority' => 'high',
                'sizes' => '(min-width: 1240px) 1160px, (min-width: 961px) calc(100vw - 80px), calc(100vw - 32px)',
            ] : []) }}
            <div class="pins">@inner($block)</div>
        </div>
        @if (filled($block->caption) || Mainstay::marking())
            <figcaption>@field($block, 'caption')</figcaption>
        @endif
    </figure>
@endif
