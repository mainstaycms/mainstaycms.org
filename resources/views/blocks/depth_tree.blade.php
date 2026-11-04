{{-- What one read of an article follows at each depth, and the global
     beside it. --}}
<div class="panel depth-tree rv">
    <div class="depth"><span class="mono">{{ __('depth :depth', ['depth' => 0]) }}</span><span class="node2 on">Article</span><span class="node2">{{ __('its images, at every depth') }}</span></div>
    <div class="depth" style="--level: 1"><span class="mono">{{ __('depth :depth', ['depth' => 1]) }}</span><span class="node2">{{ __('its related articles') }}</span><span class="node2">{{ __('its tags') }}</span></div>
    <div class="depth" style="--level: 2"><span class="mono">{{ __('depth :depth', ['depth' => 2]) }}</span><span class="node2 dashed">{{ __('what those point at') }}</span></div>
    <div class="tile depth-global"><span><strong>Layout</strong> <span class="muted">· {{ __('a global holding the menu and the footer') }}</span></span><span class="badge">{{ __('one per site') }}</span></div>
    <p class="muted">{{ __('A target in the trash, or gone, reads as missing and keeps its place, rather than failing the read.') }}</p>
</div>
