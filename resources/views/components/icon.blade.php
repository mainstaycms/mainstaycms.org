@props(['symbol', 'size' => 20])
@use('App\Blocks\Symbol')
@php
    $symbol = $symbol instanceof Symbol ? $symbol : Symbol::tryFrom((string) $symbol);
@endphp
@if ($symbol)
    <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $symbol->path() }}"/></svg>
@endif
