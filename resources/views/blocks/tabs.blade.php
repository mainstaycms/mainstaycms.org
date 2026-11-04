{{-- The code, and a tab of what it makes beside it, shown one at a time by
     the page's script: without it every tab is shown, under its heading. --}}
<div class="tabs" data-tabs>
    <div class="seg rv" role="tablist" aria-label="{{ $block->caption }}" hidden>
        @foreach ($block->inner() as $index => $tab)
            <button type="button" role="tab" id="tab-{{ $tab->id }}" aria-controls="panel-{{ $tab->id }}" aria-selected="{{ $index === 0 ? 'true' : 'false' }}">{{ $tab->label }}</button>
        @endforeach
    </div>
    <div class="out rv">
        <x-code-box :code="$block->code" :caption="$block->caption" />
        <div class="tab-panels">@inner($block)</div>
    </div>
</div>
