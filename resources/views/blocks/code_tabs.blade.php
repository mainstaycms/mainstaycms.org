{{-- One box, a tab for each sample named after its file; the page's script
     shows one at a time, and without it every sample is shown under its
     name. --}}
<div class="code-tabs rv" data-tabs>
    <div class="ctabs" role="tablist" hidden>
        @foreach ($block->inner() as $index => $sample)
            <button type="button" class="ctab" role="tab" id="tab-{{ $sample->id }}" aria-controls="panel-{{ $sample->id }}" aria-selected="{{ $index === 0 ? 'true' : 'false' }}">{{ basename((string) $sample->caption) }}</button>
        @endforeach
    </div>
    <div class="code-tabs-panels">@inner($block)</div>
</div>
