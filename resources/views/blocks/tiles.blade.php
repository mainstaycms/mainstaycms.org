@use('Mainstay\Facades\Mainstay')
<div class="tiles tiles--{{ $block->style }} rv">
    @if (filled($block->heading) || ($block->style === 'rows' && Mainstay::marking()))
        <p class="tiles-heading">@field($block, 'heading')</p>
    @endif
    <div class="tiles-list">@inner($block)</div>
</div>
