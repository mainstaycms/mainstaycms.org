@use('App\Code\Inline')
@use('Illuminate\Support\Str')
@use('Mainstay\Facades\Mainstay')
@php
    $href = $block->link === null || $block->link->missing ? null : $block->link->url();
    $id = 'section-'.Str::slug($block->heading);
@endphp
<section @class(['sec', 'wrap', 'sec-side' => $block->layout === 'side']) aria-labelledby="{{ $id }}">
    <div @class(['sec-head', 'rv', 'bare' => blank($block->lede) && ! Mainstay::marking()])>
        <h2 class="h2" id="{{ $id }}">@field($block, 'heading')</h2>
        <div class="sec-aside">
            @if (filled($block->lede) || Mainstay::marking())
                <p class="lede">{{ Inline::field($block, 'lede') }}</p>
            @endif
            @if ($href && filled($block->linkLabel))
                <a @class(['link' => filled($block->lede), 'btn btn-line' => blank($block->lede)]) href="{{ $href }}">@field($block, 'linkLabel') <span class="arr" aria-hidden="true">&rarr;</span></a>
            @endif
        </div>
    </div>
    <div class="sec-body">@inner($block)</div>
</section>
