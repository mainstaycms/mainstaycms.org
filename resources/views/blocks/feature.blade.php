@use('App\Code\Inline')
@use('Illuminate\Support\Str')
@use('Mainstay\Facades\Mainstay')
@php
    $href = $block->link === null || $block->link->missing ? null : $block->link->url();
@endphp
<div class="fsec" id="{{ $block->anchor ?: Str::slug($block->label) }}">
    <div class="fhead rv">
        <div><span class="num" aria-hidden="true"></span><h2>@field($block, 'heading')</h2></div>
        <div>
            @if (filled($block->text) || Mainstay::marking())
                <p>{{ Inline::field($block, 'text') }}</p>
            @endif
            @if ($href && filled($block->linkLabel))
                <a class="link" href="{{ $href }}">@field($block, 'linkLabel') <span class="arr" aria-hidden="true">&rarr;</span></a>
            @endif
        </div>
    </div>
    <div class="fbody">@inner($block)</div>
</div>
