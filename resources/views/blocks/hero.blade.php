@use('App\Code\Inline')
@use('Mainstay\Facades\Mainstay')
@php
    /* Each line of the heading lifts into view a little after the one above. */
    $lines = array_values(array_filter(preg_split('/\R/', $block->heading), fn (string $line) => trim($line) !== ''));
@endphp
<section @class(['hero', 'wrap', 'has-code' => filled($block->code), 'split' => $block->layout === 'split', 'halves' => $block->layout === 'halves'])>
    <div class="hero-head">
        @if (filled($block->eyebrow) || Mainstay::marking())
            <p class="eyebrow fadein">@field($block, 'eyebrow')</p>
        @endif
        <h1 class="h1">
            @if (Mainstay::marking())
                @field($block, 'heading')
            @else
                @foreach ($lines as $index => $line)
                    <span class="hl"><span style="animation-delay: {{ $index * 0.09 }}s">{{ $line }}</span></span>
                @endforeach
            @endif
        </h1>
    </div>
    <div class="hero-body fadein">
        @if (filled($block->lede) || Mainstay::marking())
            <p class="lede">{{ Inline::field($block, 'lede') }}</p>
        @endif
        <div class="actions">@inner($block)</div>
        @if (filled($block->status) || Mainstay::marking())
            <p class="status"><i aria-hidden="true"></i>@field($block, 'status')</p>
        @endif
    </div>
    @if (filled($block->code))
        <div class="hero-code fadein">
            <x-code-box :code="$block->code" :caption="$block->caption" />
        </div>
    @endif
</section>
