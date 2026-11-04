@use('Mainstay\Facades\Mainstay')
@if ($block->image?->missing === false || Mainstay::marking())
    <figure @class(['image', 'rv' => $block->style !== 'tilt'])>
        <div @class(['frame' => $block->style !== 'plain', 'crop' => $block->style === 'plain', 'tilt' => $block->style === 'tilt'])>
            {{ $block->image?->picture('wide') }}
            <div class="pins">@inner($block)</div>
        </div>
        @if (filled($block->caption) || Mainstay::marking())
            <figcaption>@field($block, 'caption')</figcaption>
        @endif
    </figure>
@endif
