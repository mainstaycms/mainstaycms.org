@php
    /* After the command, each line a little after the one above it. */
    $lines = blank($block->output) ? [] : preg_split('/\R/', rtrim($block->output));
    $delay = fn (int $line) => number_format(0.2 + $line * 0.3, 1);
    /* Artisan's own label at a line's start says how it went. */
    $tone = fn (string $line) => ['INFO' => 'pass', 'ERROR' => 'fail'][strtok(trim($line), ' ')] ?? null;
@endphp
<div class="cb term rv">
    <div class="cbh">
        <span>{{ __('terminal') }}</span>
        <button type="button" class="copy" data-copy data-text="{{ $block->command }}" data-copied="{{ __('Copied') }}" aria-live="polite" hidden><x-copy-icon />{{ __('Copy') }}</button>
    </div>
    <pre tabindex="0"><code><span class="cl" style="animation-delay: .2s"><span class="t-comment">$</span> @field($block, 'command')</span>@foreach ($lines as $index => $line)<span @class(['cl', $tone($line)]) style="animation-delay: {{ $delay($index + 1) }}s">{{ $line }}</span>@endforeach<span class="cl" style="animation-delay: {{ $delay(count($lines) + 1) }}s"><span class="t-comment">$</span> <span class="blink">▍</span></span></code></pre>
</div>
