@use('Mainstay\Facades\Mainstay')
@php
    $front = Mainstay::frontPage();
@endphp
<x-layout :title="__('Page not found')">
    <header class="wrap page-head">
        <p class="eyebrow">404</p>
        <h1 class="h1">{{ __('Page not found') }}</h1>
        <p class="lede">{{ __('There is no page at this address.') }}</p>
        <div class="actions"><a class="btn btn-acc" href="{{ ($front ? Mainstay::findById(...$front)?->url() : null) ?? '/' }}">{{ __('Back to the start') }} <span class="arr" aria-hidden="true">&rarr;</span></a></div>
    </header>
</x-layout>
