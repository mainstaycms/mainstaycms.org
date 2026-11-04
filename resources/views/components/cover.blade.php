@props(['article', 'accent' => false, 'class' => null])
{{-- An article's cover: its few characters of code over Mainstay's mark,
     drawn rather than an image, in the accent colour for the one shown
     largest. --}}
<div @class(['cover', 'acc' => $accent, $class]) aria-hidden="true">
    <x-mark class="mark" />
    <span class="glyph">{{ $article->glyph }}</span>
</div>
