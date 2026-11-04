@use('Mainstay\Facades\Mainstay')
@php
    $href = $block->href();
    $attribute = $href === null ? '' : ' href="'.e($href).'"';
@endphp
@if ($href !== null || Mainstay::marking())
    @if ($block->style === 'link')
        <a class="link"{!! $attribute !!}>@field($block, 'label') <span class="arr" aria-hidden="true">&rarr;</span></a>
    @else
        <a @class(['btn', 'btn-acc' => $block->style === 'button', 'btn-line' => $block->style === 'secondary']){!! $attribute !!}>@field($block, 'label')@if ($block->style === 'button') <span class="arr" aria-hidden="true">&rarr;</span>@endif</a>
    @endif
@endif
