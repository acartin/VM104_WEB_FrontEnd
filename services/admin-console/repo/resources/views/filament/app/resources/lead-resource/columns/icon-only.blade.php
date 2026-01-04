<div class="flex items-center justify-center">
    @php
        $icon = $icon ?? 'circle';
        $color = $color ?? '#9ca3af';
        $tooltip = $getState();
    @endphp

    <div style="color: {{ $color }};" title="{{ $tooltip }}" class="transition-transform hover:scale-110">
        <i data-lucide="{{ $icon }}" class="w-5 h-5"></i>
    </div>
</div>
