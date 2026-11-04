@use('App\Code\Inline')
@php
    $href = $block->href();
@endphp
<a class="feat rv" @if ($href) href="{{ $href }}" @endif>
    <span class="n" aria-hidden="true"></span>
    <span class="ft">@field($block, 'title')</span>
    <span class="fd">{{ Inline::field($block, 'text') }}</span>
    <span class="go" aria-hidden="true">&rarr;</span>
</a>
