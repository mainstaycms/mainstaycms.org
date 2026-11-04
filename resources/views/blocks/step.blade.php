@use('App\Code\Inline')
@use('Mainstay\Facades\Mainstay')
<div class="reqrow">
    <span class="step" aria-hidden="true"></span>
    <div class="reqrow-text">
        <div>
            <p class="reqrow-title">@field($block, 'title')</p>
            @if (filled($block->text) || Mainstay::marking())
                <p>{{ Inline::field($block, 'text') }}</p>
            @endif
        </div>
        @if (filled($block->badge) || Mainstay::marking())
            <span class="badge mono">@field($block, 'badge')</span>
        @endif
    </div>
</div>
