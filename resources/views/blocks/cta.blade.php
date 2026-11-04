@use('App\Code\Inline')
@use('Mainstay\Facades\Mainstay')
<section class="wrap sec">
    <div class="cta rv">
        <div>
            <h2 class="h2">@field($block, 'heading')</h2>
            @if (filled($block->text) || Mainstay::marking())
                <p>{{ Inline::field($block, 'text') }}</p>
            @endif
            <div class="actions">@inner($block)</div>
        </div>
        @if (filled($block->code))
            <x-code-box :code="$block->code" :caption="$block->caption" />
        @endif
    </div>
</section>
