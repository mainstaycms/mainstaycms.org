@props(['code', 'caption' => null, 'class' => null, 'id' => null])
@use('App\Code\Highlighter')
{{-- Code in a dark box, coloured, under the name of its file and a button
     copying it, which the page's script shows once it finds a clipboard to
     copy to. The <pre> takes the focus so a line wider than the box scrolls
     from the keyboard too. --}}
<div @class(['cb', $class]) @if ($id) id="{{ $id }}" @endif>
    <div class="cbh">
        <span>{{ $caption }}</span>
        <button type="button" class="copy" data-copy data-copied="{{ __('Copied') }}" aria-live="polite" hidden><x-copy-icon />{{ __('Copy') }}</button>
    </div>
    <pre tabindex="0"><code>{!! Highlighter::html($code) !!}</code></pre>
</div>
