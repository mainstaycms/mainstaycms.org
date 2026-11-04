@props(['date'])
<time datetime="{{ $date->toDateString() }}" {{ $attributes }}>{{ $date->isoFormat('D MMMM YYYY') }}</time>
