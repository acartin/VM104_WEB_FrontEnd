<div class="flex items-center gap-2 px-4 py-0.5">
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
        
        $scoreColor = $hexMap[$color] ?? '#94a3b8';
        $offset = 88 * (100 - $score) / 100;
        $glowClass = $color === 'thermal-extreme' ? 'hot-lead-glow' : '';
    @endphp

    <div class="relative flex items-center justify-center w-9 h-9 {{ $glowClass }}">
        <svg width="36" height="36" class="transform -rotate-90">
            <circle cx="18" cy="18" r="14" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-200 dark:text-gray-800"></circle>
            <circle cx="18" cy="18" r="14" fill="none" stroke="{{ $scoreColor }}" stroke-width="2.5" 
                stroke-dasharray="88" stroke-dashoffset="{{ $offset }}" stroke-linecap="round"
                style="transition: stroke-dashoffset 0.5s ease;"></circle>
        </svg>
        <span class="absolute font-black" style="font-size: 11px !important; color: {{ $scoreColor }}; text-shadow: 0 0 1px rgba(0,0,0,0.1);">{{ $score }}</span>
    </div>
    
    <div class="flex flex-col">
        <span class="text-sm font-semibold text-gray-950 dark:text-white leading-tight">
            {{ $getRecord()->full_name }}
        </span>
    </div>
</div>
