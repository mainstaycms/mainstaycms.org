<div class="panel" role="tabpanel" id="panel-{{ $block->id }}" aria-labelledby="tab-{{ $block->id }}">
    <div class="panel-head">
        <h3 class="h3">@field($block, 'heading')</h3>
        @if (filled($block->badge))
            <span class="badge mono">@field($block, 'badge')</span>
        @endif
    </div>
    <div class="panel-body">@inner($block)</div>
</div>
