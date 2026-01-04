<div class="flex justify-center">
    @php
        $record = $getRecord();
        // Access relationships directly loaded in memory (Eager Loaded)
        $def = match($type ?? '') {
            'engagement' => $record->engagementDef,
            'finance'    => $record->financeDef,
            'match'      => $record->matchDef,
            'info'       => $record->infoDef,
            default      => null
        };
    @endphp

    <div class="flex items-center justify-center transition-transform hover:scale-110 {{ $def?->color ?? 'text-gray-400' }}" 
         title="{{ $label ?? 'Score' }}: {{ $getState() }} pts">
        <i data-lucide="{{ $def?->icon ?? 'help-circle' }}" class="w-5 h-5"></i>
    </div>
</div>
