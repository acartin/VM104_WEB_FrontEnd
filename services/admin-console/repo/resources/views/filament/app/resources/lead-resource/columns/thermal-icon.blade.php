<div class="flex items-center justify-center p-1">
    @php
        $val = (int) ($getState() ?? 0);
        $record = $getRecord();
        $cat = $type ?? 'default';
        
        // Obtenemos los metadatos directamente de la base de datos
        // según el tipo de columna
        $def = match($cat) {
            'engagement' => $record->engagementDef,
            'finance' => $record->financeDef,
            'match' => $record->matchDef,
            'info' => $record->infoDef,
            default => null
        };

        $iconName = $def?->icon ?? 'help-circle';
        $cssClass = $def?->color ?? 'thermal-none';
    @endphp

    <div class="flex items-center justify-center transition-all duration-300 transform hover:scale-125 cursor-pointer {{ $cssClass }}" 
         x-tooltip="{
            content: '{{ $label ?? 'Score' }}: {{ $val }} pts',
            theme: 'dark'
         }">
        <i data-lucide="{{ $iconName }}" class="w-5 h-5"></i>
    </div>
</div>
