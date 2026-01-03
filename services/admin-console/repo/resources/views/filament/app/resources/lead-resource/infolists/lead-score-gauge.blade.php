@php
    $record = $getRecord();
    $score = $record->score_total ?? 0;
    $priority = $record->priorityDef;
    $color = $priority?->color ?? 'thermal-none';
    
    $hexMap = [
        'thermal-extreme' => '#ef4444',
        'thermal-high'    => '#f97316',
        'thermal-mid'     => '#f59e0b',
        'thermal-low'     => '#eab308',
        'thermal-none'    => '#94a3b8',
    ];
    $activeHex = $hexMap[$color] ?? '#94a3b8';
    
    $percentage = min(100, max(0, $score));
    $needleRotation = -90 + ($percentage * 1.8);
@endphp

<div class="flex items-center justify-between gap-6 py-2 px-1 group">
    <!-- Smaller Gauge -->
    <div class="relative w-40 h-20 overflow-hidden">
        <svg viewBox="0 0 100 50" class="w-full h-full overflow-visible">
            <defs>
                <linearGradient id="thermalGaugeGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                    <stop offset="0%" stop-color="#94a3b8" />
                    <stop offset="25%" stop-color="#eab308" />
                    <stop offset="50%" stop-color="#f59e0b" />
                    <stop offset="75%" stop-color="#f97316" />
                    <stop offset="100%" stop-color="#ef4444" />
                </linearGradient>
            </defs>

            <path d="M 10,50 A 40,40 0 0 1 90,50" fill="none" stroke="currentColor" 
                  stroke-width="12" stroke-linecap="round" class="text-gray-100 dark:text-gray-800" />

            <path d="M 10,50 A 40,40 0 0 1 90,50" 
                  fill="none" 
                  stroke="url(#thermalGaugeGradient)" 
                  stroke-width="12" 
                  stroke-linecap="round"
                  stroke-dasharray="125.66"
                  stroke-dashoffset="{{ 125.66 * (1 - ($percentage / 100)) }}"
                  class="transition-all duration-1000 ease-out" />

            <!-- Compact Needle -->
            <g transform="rotate({{ $needleRotation }}, 50, 50)" class="transition-transform duration-1000 ease-out">
                <path d="M 48.5,50 L 50,15 L 51.5,50 Z" fill="{{ $activeHex }}" />
                <circle cx="50" cy="50" r="4" fill="{{ $activeHex }}" stroke="white" stroke-width="1.5" />
            </g>
            <circle cx="50" cy="50" r="1.5" fill="white" />
        </svg>
    </div>

    <!-- Massive Number (Right Aligned) -->
    <div class="flex flex-col items-end leading-none">
        <span style="font-size: 5.5rem !important; line-height: 1 !important; letter-spacing: -0.05em !important;" 
              class="font-black {{ $color }} animate-in fade-in zoom-in duration-700">
            {{ $score }}
        </span>
        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.3em] opacity-50 pr-1">Scoring Index</span>
    </div>
</div>
