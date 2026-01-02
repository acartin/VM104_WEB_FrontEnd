<div class="flex items-center gap-2 px-4 py-0.5">
    @php
        $score = $getRecord()->score_total ?? 0;
        $scoreColor = '#3b82f6'; // Cold (blue-500)
        $glowClass = '';
        
        if ($score >= 90) {
            $scoreColor = '#ef4444'; // Hot (red-500)
            $glowClass = 'hot-lead-glow';
        } elseif ($score >= 70) {
            $scoreColor = '#f59e0b'; // Warm (amber-500)
        } elseif ($score >= 50) {
            $scoreColor = '#10b981'; // Qualified (emerald-500)
        }
        
        $offset = 88 * (100 - $score) / 100;
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
        <span class="text-sm font-medium text-gray-500 dark:text-gray-400 leading-tight">
            {{ $getRecord()->full_name }}
        </span>
    </div>
</div>
