@use('App\Code\Inline')
@use('Mainstay\Facades\Mainstay')
@php
    $href = $block->href();
@endphp
<{{ $href ? 'a' : 'div' }} class="card rv" @if ($href) href="{{ $href }}" @endif>
    @if ($block->icon)
        <span class="ic"><x-icon :symbol="$block->icon" :size="18" /></span>
    @endif
    <h3 class="h3">@field($block, 'title')</h3>
    @if (filled($block->text) || Mainstay::marking())
        <p>{{ Inline::field($block, 'text') }}</p>
    @endif
</{{ $href ? 'a' : 'div' }}>
