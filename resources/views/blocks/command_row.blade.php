@use('App\Code\Inline')
@use('Mainstay\Facades\Mainstay')
<div class="cmd">
    <code class="cmd-name">@field($block, 'command')</code>
    <span class="cmd-text">{{ Inline::field($block, 'text') }}</span>
    @if (filled($block->badge) || Mainstay::marking())
        <span class="badge">@field($block, 'badge')</span>
    @else
        <span></span>
    @endif
    <button type="button" class="copy" data-copy data-text="php artisan {{ $block->command }}" data-copied="{{ __('Copied') }}" aria-label="{{ __('Copy php artisan :command', ['command' => $block->command]) }}" aria-live="polite" hidden><x-copy-icon />{{ __('Copy') }}</button>
</div>
