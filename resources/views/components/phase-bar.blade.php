@props(['phases'])
{{-- A segment per phase, filled once it is done. --}}
<div class="bar16" role="img" aria-label="{{ __(':done of :all phases done', ['done' => $phases->where('done', true)->count(), 'all' => $phases->count()]) }}" style="--phases: {{ max($phases->count(), 1) }}">
    @foreach ($phases as $index => $phase)
        <i @class(['todo' => ! $phase->done]) style="animation-delay: {{ number_format(0.4 + $loop->index * 0.04, 2) }}s"></i>
    @endforeach
</div>
