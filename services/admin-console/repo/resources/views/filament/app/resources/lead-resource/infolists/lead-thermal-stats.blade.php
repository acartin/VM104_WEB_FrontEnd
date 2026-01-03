@php
    $record = $getRecord();
    
    // Mapeo de colores térmicos a clases Tailwind bg- (Sincronizado con Doc)
    $bgMap = [
        'thermal-extreme' => 'bg-red-500',
        'thermal-high'    => 'bg-orange-500',
        'thermal-mid'     => 'bg-amber-500',
        'thermal-low'     => 'bg-yellow-500',
        'thermal-none'    => 'bg-gray-400',
        'thermal-slate-500' => 'bg-slate-500',
        'thermal-slate-600' => 'bg-slate-600',
        'thermal-slate-700' => 'bg-slate-700',
        'thermal-slate-800' => 'bg-slate-800',
        'thermal-finance-extreme' => 'bg-emerald-400',
        'thermal-finance-high'    => 'bg-emerald-500',
        't-inmediato'     => 'bg-red-500',
        't-caliente'      => 'bg-red-400',
        't-tibio'         => 'bg-orange-400',
        't-medio'         => 'bg-amber-400',
        't-indefinido'    => 'bg-blue-500',
        't-largo'         => 'bg-slate-400',
        't-frio'          => 'bg-slate-600',
    ];

    $stats = [
        [
            'label' => '1. Interés y Engagement',
            'value' => $record->engagementDef?->meaning ?? 'Sin datos',
            'score' => $record->score_engagement,
            'icon'  => $record->engagementDef?->icon ?? 'help-circle',
            'color' => $record->engagementDef?->color ?? 'thermal-none',
            'max'   => 30
        ],
        [
            'label' => '2. Capacidad Financiera',
            'value' => $record->financeDef?->meaning ?? 'Sin datos',
            'score' => $record->score_finance,
            'icon'  => $record->financeDef?->icon ?? 'help-circle',
            'color' => $record->financeDef?->color ?? 'thermal-none',
            'max'   => 30
        ],
        [
            'label' => '3. Timeline / Urgencia',
            'value' => $record->timelineDef?->meaning ?? 'Sin datos',
            'score' => $record->score_timeline,
            'icon'  => $record->timelineDef?->icon ?? 'clock',
            'color' => $record->timelineDef?->color ?? 'thermal-none',
            'max'   => 20
        ],
        [
            'label' => '4. Match / Inventario',
            'value' => $record->matchDef?->meaning ?? 'Sin datos',
            'score' => $record->score_match,
            'icon'  => $record->matchDef?->icon ?? 'home',
            'color' => $record->matchDef?->color ?? 'thermal-none',
            'max'   => 15
        ],
        [
            'label' => '5. Info / Calidad de Datos',
            'value' => $record->infoDef?->meaning ?? 'Sin datos',
            'score' => $record->score_info,
            'icon'  => $record->infoDef?->icon ?? 'info',
            'color' => $record->infoDef?->color ?? 'thermal-none',
            'max'   => 5
        ],
    ];
@endphp

<div class="grid grid-cols-1 gap-1 mt-1">
    @foreach($stats as $stat)
        <div class="flex items-center gap-3 py-1.5 px-2 rounded-lg transition-all duration-300 group">
            @php
                $bgClass = $bgMap[$stat['color']] ?? 'bg-gray-300';
            @endphp
            
            <div class="shrink-0 p-2.5 rounded-lg bg-white dark:bg-gray-800 shadow-sm flex items-center justify-center transition-all duration-300 {{ $stat['color'] }} group-hover:scale-110">
                <i data-lucide="{{ $stat['icon'] }}" class="w-5 h-5"></i>
            </div>
            
            <div class="flex items-center gap-3 flex-1 min-w-0">
                <!-- Data Row: Meaning and Score -->
                <div class="flex items-center justify-between flex-1">
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400 leading-tight truncate max-w-[70%]">
                        {{ $stat['value'] }}
                    </span>
                    <span class="text-lg font-black {{ $stat['color'] }} flex-shrink-0">
                        {{ $stat['score'] }}
                    </span>
                </div>
                
                <!-- Vertical Progress Meter -->
                @php
                    $percentage = ($stat['max'] > 0) ? round(($stat['score'] / $stat['max']) * 100) : 0;
                    $meterColorHex = match(true) {
                        $percentage >= 80 => '#10b981',  // green
                        $percentage >= 60 => '#eab308',  // yellow
                        $percentage >= 40 => '#f97316',  // orange
                        default => '#ef4444'             // red
                    };
                @endphp
                <div style="display: flex; flex-direction: column; gap: 4px; align-items: center; flex-shrink: 0;">
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: flex-end; height: 48px; width: 8px; background-color: #d1d5db; border-radius: 9999px; overflow: hidden; border: 1px solid #9ca3af;">
                        <div style="width: 100%; height: {{ $percentage }}%; background-color: {{ $meterColorHex }}; transition: all 0.5s ease-out; min-height: 2px;"
                             x-tooltip="{
                                 content: '{{ $percentage }}% ({{ $stat['score'] }}/{{ $stat['max'] }})',
                                 theme: 'dark'
                             }">
                        </div>
                    </div>
                    <span style="font-size: 11px; color: #6b7280; font-family: monospace; font-weight: 500;">{{ $percentage }}%</span>
                </div>
            </div>
        </div>
    @endforeach
</div>

<script>
    if (window.lucide) {
        window.lucide.createIcons();
    }
</script>
