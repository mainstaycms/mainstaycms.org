@use('App\Code\Inline')
@use('Mainstay\Facades\Mainstay')
<article class="card plugin" data-adds="{{ json_encode($block->additions()) }}" data-words="{{ mb_strtolower($block->name.' '.$block->package.' '.$block->text) }}">
    <div class="plugin-head">
        <span class="ic">@if ($block->icon)<x-icon :symbol="$block->icon" :size="22" />@endif</span>
        <div>
            <h2 class="h3">@field($block, 'name')</h2>
            <span class="pkg mono">@field($block, 'package')</span>
        </div>
    </div>
    @if (filled($block->text) || Mainstay::marking())
        <p>{{ Inline::field($block, 'text') }}</p>
    @endif
    <div class="badges">
        @foreach ($block->additions() as $addition)
            <span class="badge">{{ $addition }}</span>
        @endforeach
    </div>
    <div class="pfoot">
        @if ($block->example)
            <span class="badge dashed">{{ __('Example') }}</span>
        @else
            <div class="install"><span class="prompt" aria-hidden="true">$</span><code>composer require {{ $block->package }}</code><button type="button" class="copy" data-copy data-copied="{{ __('Copied') }}" aria-label="{{ __('Copy the command') }}" aria-live="polite" hidden><x-copy-icon />{{ __('Copy') }}</button></div>
        @endif
    </div>
</article>
