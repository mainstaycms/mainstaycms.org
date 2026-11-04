@use('Illuminate\Support\Str')
<section class="wrap">
    <div class="layout">
        <nav class="toc" aria-label="{{ __('On this page') }}" data-contents>
            @foreach ($block->inner() as $index => $feature)
                <a href="#{{ $feature->anchor ?: Str::slug($feature->label) }}"><span>{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span><span>{{ $feature->label }}</span></a>
            @endforeach
        </nav>
        <div class="main">@inner($block)</div>
    </div>
</section>
