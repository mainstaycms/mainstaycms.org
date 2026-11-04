@use('App\Code\Inline')
@use('Mainstay\Facades\Mainstay')
<div class="chapter">
    <div class="chapter-text rv">
        <span class="sn" aria-hidden="true"></span>
        <h2>@field($block, 'heading')</h2>
        @if (filled($block->text) || Mainstay::marking())
            <p>{{ Inline::field($block, 'text') }}</p>
        @endif
    </div>
    <div class="chapter-media">@inner($block)</div>
</div>
