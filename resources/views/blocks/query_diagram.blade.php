{{-- Four readers going through one query layer, Gate asked on the way, to
     the tables: dots run along the lines while motion is allowed. --}}
<div class="panel diagram rv">
    <svg viewBox="0 0 760 300" width="100%" role="img" aria-label="{{ __('Blade templates, seeders, the admin and the content API all read through the query layer, Gate asked first, into the database\'s tables') }}">
        <g fill="none" stroke="var(--accent)" stroke-width="1.4">
            <path class="flow" d="M150 46 C 270 46, 270 150, 330 150"/>
            <path class="flow" d="M150 114 C 270 114, 270 150, 330 150"/>
            <path class="flow" d="M150 186 C 270 186, 270 150, 330 150"/>
            <path class="flow" d="M150 254 C 270 254, 270 150, 330 150"/>
            <path class="flow" d="M520 150 L 620 150"/>
            <path d="M425 190 V214"/>
        </g>
        <g font-family="var(--sans)" font-size="14">
            @foreach ([26 => __('Blade templates'), 94 => __('Seeders'), 166 => __('The admin'), 234 => __('Content API')] as $y => $reader)
                <rect x="20" y="{{ $y }}" width="130" height="40" rx="10" fill="var(--canvas)" stroke="var(--border)"/>
                <text x="85" y="{{ $y + 25 }}" text-anchor="middle" fill="var(--ink)">{{ $reader }}</text>
            @endforeach
            <rect x="330" y="110" width="190" height="80" rx="14" fill="var(--code)"/>
            <text x="425" y="144" text-anchor="middle" fill="var(--c-at)" font-family="var(--mono)" font-size="13">Mainstay::find()</text>
            <text x="425" y="168" text-anchor="middle" fill="var(--c-cm)" font-size="12">{{ __('one query layer') }}</text>
            <rect x="375" y="214" width="100" height="32" rx="16" fill="var(--accent)"/>
            <text x="425" y="235" text-anchor="middle" fill="var(--accent-ink)" font-size="13">Gate</text>
            <ellipse cx="672" cy="124" rx="46" ry="12" fill="var(--canvas)" stroke="var(--ink)" stroke-width="1.2"/>
            <path d="M626 124 v52 a46 12 0 0 0 92 0 v-52" fill="var(--canvas)" stroke="var(--ink)" stroke-width="1.2"/>
            <text x="672" y="160" text-anchor="middle" fill="var(--muted)" font-size="12">{{ __('real tables') }}</text>
        </g>
        <circle r="4.5" fill="var(--accent)" class="dot" style="offset-path: path('M150 46 C 270 46, 270 150, 330 150')"/>
        <circle r="4.5" fill="var(--accent)" class="dot" style="offset-path: path('M150 186 C 270 186, 270 150, 330 150'); animation-delay: 1.1s"/>
        <circle r="4.5" fill="var(--accent)" class="dot" style="offset-path: path('M150 254 C 270 254, 270 150, 330 150'); animation-delay: 2.2s"/>
        <circle r="4.5" fill="var(--accent)" class="dot" style="offset-path: path('M520 150 L 620 150'); animation-delay: .6s"/>
    </svg>
</div>
