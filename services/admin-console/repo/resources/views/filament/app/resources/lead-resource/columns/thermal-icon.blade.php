<div class="flex items-center justify-center p-1">
    @php
        $val = (int) ($getState() ?? 0);
        $record = $getRecord();
        $cat = $type ?? 'default';
        
        // Obtenemos los metadatos directamente de la base de datos
        // según el tipo de columna
        $iconName = match($cat) {
            'engagement' => $record->eng_icon,
            'finance' => $record->fin_icon,
            'match' => $record->match_icon,
            'info' => $record->info_icon,
            default => 'help-circle'
        };

        $cssClass = match($cat) {
            'engagement' => $record->eng_color,
            'finance' => $record->fin_color,
            'match' => $record->match_color,
            'info' => $record->info_color,
            default => 'thermal-none'
        };
    @endphp

    <div class="flex items-center justify-center transition-all duration-300 transform hover:scale-125 cursor-pointer {{ $cssClass }}" 
         x-tooltip="{
            content: '{{ $label ?? 'Score' }}: {{ $val }} pts',
            theme: 'dark'
         }">
        <i data-lucide="{{ $iconName }}" class="w-5 h-5"></i>
    </div>
</div>
