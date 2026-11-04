@use('App\Blocks\DocsSection')
@use('App\Code\Inline')
@use('App\Content\Doc')
@use('Mainstay\Facades\Mainstay')
@php
    $docs = Mainstay::find(Doc::class, where: ['section' => $block->section], sort: 'position', depth: 0);
@endphp
<div class="card guide rv">
    <span class="ic"><x-icon :symbol="DocsSection::SYMBOLS[$block->section]" /></span>
    <h3 class="h3">{{ Doc::SECTIONS[$block->section] }}</h3>
    @if (filled($block->text) || Mainstay::marking())
        <p>{{ Inline::field($block, 'text') }}</p>
    @endif
    @if ($docs->isNotEmpty())
        <ul class="items">
            @foreach ($docs as $doc)
                <li><a href="{{ $doc->url() }}">{{ $doc->title }}<span class="arr" aria-hidden="true">&rarr;</span></a></li>
            @endforeach
        </ul>
    @endif
</div>
